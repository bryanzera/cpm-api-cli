# CPM-CMS Command Line Tool

This is a tool to create and edit objects in the CPM CMS via the command line.

## Requirements
- `php` with `curl` libraries installed

## Installation
- Clone this repository where you wish to install `cpmapi`
- Copy `config.default.php` to `config.php` and insert appropriate configuration definitions.
- Make `cpmapi` executable: `chmod 755 cpmapi`

## Configure
The configuration definitions in `config.php` are:
- `CPMAPI_ENVIRONMENT`: The CMS environment to use.  Available values are `production` and `development`.
- `CPMAPI_CMS_USER`: The username to use when performing actions on the CMS.
- `CPMAPI_CMS_PASSWORD`: The password for the `CPMAPI_CMS_USER`
- `CPMAPI_NOTIFICATION_TOKEN`: The `NOTIFICATION_TOKEN` value for the [SMS Notification System](https://github.com/wbez/cpm-sms-notifications).

## Usage
`./cpmapi [VERB] [NOUN] [OPTIONS]`
- `[VERB]`: Current options are `create`, `update`
- `[NOUN]`: Current options are `story`, `audio`, `show`
- `[OPTIONS]`: Can be any of the following.
- - `--json`: Do not send to CMS.  Return the JSON object.
- - `--test-logging`: Send a test message to Slack to ensure that logging is set up correctly.
- - `--test-token`: Retrieve and display the CMS token for `CPMAPI_CMS_USER` to ensure that the CMS credentials are correct and that communication with the CMS is occurring correctly.
- - `--org:[OBJECT-PATH]=[VALUE]`: The value to set for a particular `[OBJECT-PATH]` of the CMS `[NOUN]` that is being `[VERB]`ed.  `[OBJECT-PATH]` sections are separated by `:`.  Numeric `[OBJECT-PATH]` parts designate arrays.

## Return Value

On success, the exit status is set to 0 and the CMS ID of the new object is returned.  

On failure, the exit status is set to 1 and any available error messages are returned.

## Example

```
./cpmapi create story \
--obj:owner="9d14bf26-6639-4e2d-a269-b3681ff2980e" \
--obj:writable=true \
--obj:status="published" \
--obj:title="Test Post Insert via API" \
--obj:displayTitle="Test Post Insert via API" \
--obj:teaser="<p>Test Bootstrap Classes For DOM elements in CMS<br></p>" \
--obj:displayTeaser="Test Bootstrap Classes For DOM elements in CMS" \
--obj:content="Test Post Insert via API" \
--obj:show="2de96ba3-3459-42d9-bc55-f21d740c1b07" \
--obj:kicker="To Show" \
--obj:type="story" \
--obj:audio:0:id="9bc26ec4-8cf4-44d7-9498-33cb533685c5" \
--obj:topics:0="1a15d046-9429-4d53-804e-9a77b111fcda" \
--obj:topics:1="53d57c63-b555-4cc8-8d80-65fcc9f197a8"
```
