# DuskPHP - Middleware
[![Build Status](https://travis-ci.org/Hovensoft/duskphp-middleware.svg?branch=master)](https://travis-ci.org/Hovensoft/duskphp-middleware)
[![Coverage Status](https://coveralls.io/repos/github/Hovensoft/duskphp-middleware/badge.svg?branch=master)](https://coveralls.io/github/Hovensoft/duskphp-middleware?branch=master)

 Provide middleware:
 - a CSRF authenticator which protect against CSRF attack with a token authentication 

 ## How to use
 
 - ## CSRF Authenticator
 __How to use it ?__
 
 This middleware check every POST, PUT and DELETE request for a CSRF token.
 
 ```php
 $middleware = new CsrfMiddleware($_SESSION, 200);
 $dispatcher->pipe($middleware);
 ```
 __Input__
 
 The middleware check if the string `<:csrf_token_field:>` is in response and replace it with 
 the authenticator's token.
 
 ```html
 <form action="" method="post">
    ...
    <:csrf_token_field:>
</form>
```

 
 ## LICENSE
 
 DuskPHP - A simple PHP framework build with middleware pattern
 
 Copyright (C) 2017  HovenSoft
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 any later version
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
         along with this program.  If not, see [www.gnu.org/licenses](http://www.gnu.org/licenses).
         
 
