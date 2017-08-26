# DuskPHP - Middleware

 A Collection of middleware

 ## How to use
 
 ### CSRF Authentication
 This middleware check every POST, PUT and DELETE request for a CSRF token.
 
 ```php
 $middleware = new CsrfMiddleware($_SESSION, 200);
 $dispatcher->pipe($middleware);
 
 //Generate input
 $middleware->input();

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
         
 
