### Todos API

*Almost every "todo" tutorial I can find for the latest javascript or cross platform 
mobile framework either has no backend at all, or uses Firebase, or some commercial derivative
thereof.  I built this API free for anyone to use, hoping that we'll start seeing more tutorials
that take into account things like user authentication.*

#### Features:
- Built using Laravel Lumen 8 and php 8.1
- Swagger documentation
- Google Recaptcha
- Mailgun integration
- Custom Authentication middleware
- CORS middleware
- MySQL database
- Google Analytics

#### Password reset
- `POST /api/users/forgot` with `{"email"}` emails a 6 digit code (always answers the same message, whether or not the account exists)
- `POST /api/users/reset` with `{"email","code","password"}` sets a new password and signs the user out everywhere
- the code works once, expires after 15 minutes and is locked after 5 wrong attempts; one request per minute per account, 20 per day per developer key

#### Documentation
- [Register for APIKey](https://todos.simpleapi.dev)
- [Review Swagger Docs](https://todos.simpleapi.dev/docs)
