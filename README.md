# Validation-Tweaker

This REDCap module provides options to adjust how validation is performed.


## System-level configuration options

### Enable regular expression action tag
Enable this to allow use of the **@REGEX** action tag.


## Project-level configuration options

### Require field validation to pass on form submission
This will check that when forms are submitted with status set to complete, that there are no field
validation errors and no empty required fields. Submission will be blocked if there is a validation
error or an empty required field.

### Exempt specific forms from validation enforcement
If field validation is required to pass on form submission, this option allows some forms to be
exempted from the validation enforcement. Each form selected can be exempted from enforcement of
all validation, validation of required fields, or validation of values (format validation).

### Don't allow entry of dates in the future
This will raise a field validation error if a date after the current date is entered into a date or
datetime field. This can be overridden on a per-field basis with the **@ALLOWFUTURE** action tag.

### Don't allow entry of dates in the past
This will raise a field validation error if a date before a defined value is entered into a date or
datetime field. This can either be a fixed date value, or can be the date entered in a specific
field on the record. If both a fixed date and a date field are specified, the validation will be
performed using the most recent date of the two. This can be overridden on a per-field basis with
the **@ALLOWPAST** action tag.

Note that when a date field is specified, the validation will only take effect once a value *has
been submitted* for the field. This is therefore not a reliable means of validating dates on the
same form as the defined comparison field.

### Provide option on surveys to continue regardless of whether required fields are complete
On surveys, if there are required fields which have not been completed, provide the respondant with
the option to *continue anyway*. This will, following a warning, allow them to proceed despite the
survey not being complete.
