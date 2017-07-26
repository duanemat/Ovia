# Ovia
Ovia Health coding exercise - submitted by Matthew Duane

## Setup
* Should just be able to clone this directory and run the application by directing web server to the public folder OR simply going into `{project}/public` and entering 'php -S localhost:{port_number}'.
* You might need to alter permissions of storage directory to allow anyone to write to it.
* You will need the version of PHP you are using to have the `sqlite` PDO driver enabled.  You can check `php.ini` to be sure.

## Running app
All endpoints are prefixed with `v1/` because this is the first entry in the API.

There are 3 major endpoints:
* `v1/availabilty/{room_id}` [GET] - Returns available rooms to rent, or if specified specific room, only that room's availability.  Possible fields in the request:
  * start_date - first date of stay, formatted m-d-Y (default: today)
  * los - length of stay in days (default: 1)
  * num_guests - number of guests (default: 1)
  * num_luggage - number of pieces of luggage (default: 1)
  * check_in - time to check in between 12 (12 pm) and 20 (8pm) (default: 12)
  * guest_id - Guest id for this request (default: "")

* `v1/rooms/{room_id}` [POST] - Creates a booking, if possible, in the database.  If room ID specified, will try to only make booking at that room.  Will return either the room ID if booked, else an error message describing what doesn't work.   Possible fields in the request:
  * start_date - first date of stay, formatted m-d-Y (default: today)
  * los - length of stay in days (default: 1)
  * num_guests - number of guests (default: 1)
  * num_luggage - number of pieces of luggage (default: 1)
  * check_in - time to check in between 12 (12 pm) and 20 (8pm) (default: 12)
  * guest_id - Guest id for this request (default: "")

* `v1/maintenance/{maintenance_team_id}` [GET] - Returns the cleaning schedule (default team is #1; if specified, then the cleaning schedule for that team).  Required field is the date's schedule you'd like, formatted as m-d-Y.

## Further Questions
### What is going on?
                                                                                                                                                                          
It's a pretty simple application.  Every booking is stored as a Reservation in the sqlite database, along with information about each available Room, plus the cleaning crew's name, time per room and per guest cleaning.  For multi-day bookings, I simply recorded a booking for each day.  Having worked with booking data in the past, I've found a per-day transaction is easier to parse and search than a range of dates.  Plus, when you have to factor in cancellations, refunds, or other changes to the stay, having them as discreet days makes life much easier.  

Beyond that, I just need to know your starting day, the length of stay, how many guests, how much luggage, and we're off.  I added in default values, but it might make more sense to remove those if the owner doesn't want to just book people even if the call lacks all relevant information.
For booking, I check availability to see if any room fits the criteria (unless a specific room is set), then if any are available, I cycle through and try to find the "best" option. My rubric is fill partially-filled rooms first, then rooms where you'd have the least number of extraneous storage/guest spaces left over (i.e. if you have a person with no luggage, I'd put them in a 1-person, 1-luggage room over a 2-person, 1-luggage room).

Maintenance just returns an array of rooms to be cleaned, with rooms being used yesterday and being rented today given priority in the queue.

### Extending this application?
Lots of ways to extend the application.  I tried to make it as modular as possible, with the ability to add more information in the database (such as rooms and maintenance crews) and it would still work. But, for example, I don't store the maintenance schedule anywhere.  That's fine with one crew, but multiple cleaning crews would require some coordination to make sure they don't just duplicate work.  Plus, there isn't an issue with the maintenance crew not having the room ready before you come in (they'll spend less time cleaning than maximum for the day).  

In terms of endpoint design, I'd probably extend out the descriptiveness of the request such that, for example, requesting a specific's cleaning schedule wouldn't be `/v1/maintenance?cleaning_date=07-26-2017` but instead `/v1/maintenance/2017/07/26`.  This would minimize querystring bloat while making it easier to pinpoint specific dates or scenarios for requests.

Similarly, the more business logic (such as rules about when you split larger parties into different rooms for efficiency) would change the overall calculus of the application.  In a larger application, I'd probably add a model layer to better compartmentalize away things like "Reservations", "Rooms", etc., unlike here where I write directly to the database.  With more time and a deeper architecture, I'd also add, say, a queueing mechanism that would do the backend writing to the database so that, for example, two users didn't book the same room because the requests were processed nearly concurrently.  In the same vein, I'd also add in a caching layer (perhaps with Redis) so that commonly-requested information (such as maintenance schedules, room details, etc.) that rarely changed would be quicker to access and only updated in situations such as a successful booking for the affected date(s).

I'd also add that handling cancellations, changes to stays, rebalancing of future bookings to be more efficient, are all future iterations of the project that I'd focus on.  Plus, adding in User information would be good for tracking stay patterns.

### Outside help?
StackOverflow and Laracasts for minor setup issues, php.net for all of the usual function parameters lookups.  I also have previously worked on a hotelier system, so a lot of this is embedded in my soul, for better or for worse.

### Third party tools and libraries?
* Lumen - I've used it before, it's easy to set up and use for small API endpoints.  
* SQLite - Really simple and easy database to set up, portable, minor resource usage, and responsive.  

### Time Spent?
It was in fits and spurts, but I'd say about 6-10 hours total.  The problem was typically I didn't work on it until around 10 PM, which slows down my efficiency quite a bit.  If I had more time, I'd definitely focus on integrating more robust logic to optimize bookings for larger parties.  It might not make business sense, but if we are comfortable with breaking up parties, you could cut out whole rooms being used.  And as mentioned above, I'd like to see about handling instances when the cleaning crew can't clean everything in a day and people want to all check in at a certain time. I added the `check_in_time` field, but we don't have clear business logic what those times are and how flexible guests can be.

### Future Testing?
I'd definitely add a suite of both integration tests (Mock up a small database using something like `Mockery`, then book a bunch of rooms and make sure availability and maintenance keep up).  I'd also unit test it quite extensively; I have a couple Utility functions that check the database or parse inputs; I'd check those extensively to make sure they handle different inputs properly. Beyond that, I'd make sure corner conditions such as no more available rooms, or too many people for any particular room, were handled.  And undoubtedly, as more and more debugging and QA was performed on the API, I'd find areas that would need extra focus.  