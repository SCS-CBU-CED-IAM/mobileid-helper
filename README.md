mobileid-helper
===============

A helper application to check the status about a Mobile ID user.

## Requirements
* PHP 5.3.x
* PHP libcurl
* PHP libxml
* OpenSSL

## Install
Download the package and make it available to your web server.
Example: `git clone <URL> /var/www/mobileid`


## Configuration
* Rename the configuration file example from `conf/configuration.example.php` to `conf/configuration.php`
* Edit the configuration file `conf/configuration.php` according to your environment

Refer to the "Mobile ID - SOAP client reference guide" document from Swisscom for more details about configuration elements.

## Getting Started
* Open the URL of your web server -> http://webserver/mobileid/

## Advanced configuration

### Error handling
When an is sent back from the Mobile ID it will display the `APP_ERROR_DEFAULT` unless it has explicitly been defined in the `language/xx/xx.ini` file.

By default, the error is of type `error` (Red) related to a Mobile ID failure. For relevant errors, where the Mobile ID user can be helped the error type will be `warning` (Yellow).
Here the list of `warning` errors defined in `helpers/mobileid.php`:
````
$warning_code = array("105", "208", "209", "401", "402", "403", "404", "406", "422");
````

The error code 20901 (Applet Language resync) is handled with an automatic and transparent retry.

Refer to the "Mobile ID - SOAP client reference guide" document from Swisscom for more details about error states.

### Message to be signed
Is composed by "'conf/configuration.php:$mid_msg_service': 'conf/configuration.php:$mid_msg_en|de|fr|it'".
Example with:
````
	public $mid_msg_service = "service.com";
	public $mid_msg_en = "Allow testing of your Mobile ID?";
````
Will produce following message in english: "service.com: Allow testing of your Mobile ID?" 


If 'conf/configuration.php:$mid_msg_allowedit' is set, then then this message can be edited before sending.

### Translations
The actual resources are translated in EN, DE, FR, IT. Refer to the files in the `language/` folders.

### Language detection
The application detects the current browser language and uses it. If the detected language is not supported it will fallback to english.  
With the ?lang parameter the detection can be turned off and a specific language can be enforced.  
Example http://webserver/mobileid?lang=fr to force the usage of the french language.