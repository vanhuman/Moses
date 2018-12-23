# startUp
clean var_dump functionality

# install
- add in php.ini or create new ini file (e.g. /etc/php/7.2/fpm/conf.d/99-startUp.ini): 
```
  auto_prepend_file = /pathToRepository/startUp/index.php
```
- create a simple virtual host e.g. (/etc/apache2/sites-available/00-startUp.conf): 
```
<VirtualHost *:80> 
         ServerName startup.domain 
         DocumentRoot /pathToRepository/startUp/ 
         CustomLog /var/log/apache2/startup_access.log combined 
         ErrorLog /var/log/apache2/startup_errors.log
 
         <Directory /pathToRepository/startUp/> 
                 Options FollowSymLinks 
                 Order allow,deny 
                 Allow from all 
         </Directory> 
 </VirtualHost> 
 ```
 - add virtual host to apache e.g.: 
    ``` sudo a2ensite 00-startUp.conf```
 - reload apache: 
    ``` sudo service apache2 reload```
 - add domain to hosts file e.g.: 
    ```127.0.0.1 startup.domain```
 - copy startUp.ini.dist and rename to startUp.ini
    
# settings

## startUp.ini.dist
possible settings:
basePath: the path to the repository *(required)*
filesPath: the path where the dump files will be written *(optional)*



## beforeInitHook.php
create this file in the root to include functionality before startUp is loaded
 
# usage
open the monitor: startup.domain/monitor.php

## var_dump options
- simple use:
```
std($value);
```
- with label
```
std()->show($value, 'label');
std()->show($value, ['tag', 'label']);
```
- get function call arguments
```
public function test($param) {
    std()->verb();
}
```
- with backtrace
```
public function test($param) {
    std()->verb(true);
}
```
- benchmarking
```
std()->benchmark('label');
```
- change dept of processed descendants (default 3)
```
std()->set_output_depth(5); 
```

## monitor labels
- oranje: string
- white: number
- dashed line: array
- solid line: object
