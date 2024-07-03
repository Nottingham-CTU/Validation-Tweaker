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

### Provide option on surveys to continue regardless of whether required fields are complete
On surveys, if there are required fields which have not been completed, provide the respondant with
the option to *continue anyway*. This will, following a warning, allow them to proceed despite the
survey not being complete.

### Exempt specific forms from survey continue option
If the option to allow a survey submission despite incomplete required fields is selected, this
option allows some forms to be exempted from this.
