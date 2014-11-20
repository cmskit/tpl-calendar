# cms-kit template "Calendar/Resource calendar"

## Setup

A resource-calendar is defined by two objects:

* an event-object is at least defined by start- and stop-dates and -optional- fields describing a recursion and a duration. Of course it can/should hold additional fields containing more informations (eg. names, etc.) and further connections.
* at least one resource-object (connected to the event-object) with at least a string-field (eg. the name of the resource)

Here is an example of the configurations, placed in the event-object **AND** the resource-object

	{
	  "calendar": {
	    "resources": {
	      "my_resource": {
	         "titlefields": [
	           "name"
	         ],
	         "filter": []
	      }
	    },
	    "event": {
	      "objectname": "my_event",
	      "titlefields": [
	        "name"
	      ],
	      "startfield": "start",
	      "stopfield": "end",
	      "cronfield": "cron",
	      "lengthfield": "span",
	      "colorfield": "col",
	      "filter": []
	    }
	  }
	}

And here comes the explanation:

* "calendar" 
  * "resources"
      * "[resourcename]" the object-name or the resource
          * "titlefields" array holding the fieldnames concatenated as resource-name
          * "filter" not implemented
      * ... optional another resource (not tested yet)
  * "event"
      * "objectname" the objectname of the event object
      * "titlefields" array holding the fieldnames concatenated as event-name
      * "startfield" timestamp-field defining the start of the event/period
      * "stopfield" timestamp-field defining the end of the event/period
      * "cronfield" (optional) cron-field defining a recurring event (use wizard:cron)
      * "lengthfield" (optional) duratuion-field defining the length of the recurring events (use wizard:duration)
      * "colorfield" varchar-field defining the color of the event (use color as a information)
      * "filter" not implemented
