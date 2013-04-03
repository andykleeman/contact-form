contact-form
============

A working contact form example using MDS EmailSender by Mark Haskins (https://github.com/githublemming).

How this works
--------------------
Functions.js contains frond-end validation for the form, if this passes it posts an ajax call to contact_form.php

contact_form.php includes contact_data.php which passes the back end validation if this is successful an email will be sent and a success message returned to #msg.

Fields are validated using the js in functions.js - required fields should have the data-required="required" and data-field="name" attributes

Make sure you update the email to and from addresses lines 34-38 of contact_form.php