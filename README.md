#### Setup Instructions
1. Clone repo
2. create `.env` by running `.env.example`
3. run `composer install`
4. run migrations `php artisan migrate`
5. seed database `php artisan db:seed`
 

#### Batch Update Implementation

Upon each update to the user record,
I push the data to be updated to a database table called `pending_updates_requests`

I then set a key called `number_of_calls_made` in redis that expires in an hour 

I then schedule a task that runs the command `batch:updates` every minute

When this command runs, I check for the following conditions

1. `number_of_calls_made` is set and is less than `50`
2. number of records in the  `pending_updates_requests` is up to a `1000`

if the 2 conditions are satisfied, I format the data, pass it to an event then makes the batch requests
and then delete the 1000 records from the database.


Assumptions:

Suppose we run the command every minute, that will be `60` times within the hour

because we expect `40,000` updates within the hour, it will take us roughly `40 runs` in `40 minutes` to run the updates

