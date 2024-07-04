# Validation-Tweaker

This REDCap module provides options to adjust how validation is performed.


## Field validation using conditional logic and regular expressions

There are 3 action tags which can be used to provide additional field validation capabilities:

* **@VALIDATE-LOGIC** &ndash; This can be used to supply conditional logic which must be valid in
  order for the field value to be accepted.<br>
  For example, if using an *email* and *confirm email* field, the confirm field could utilise the
  action tag as follows:<br>
  `@VALIDATE-LOGIC([email] = [email_confirm])`
* **@VALIDATE-MESSAGE** &ndash; This can be used to supply a custom message to be displayed in
  place of the standard message when a field validation error is triggered by the `@VALIDATE-LOGIC`
  or `@REGEX` action tags.<br>
  For example, a *confirm email* field might use:<br>
  `@VALIDATE-MESSAGE="Sorry, the email addresses must match."`
* **@REGEX** &ndash; This can be used to define a specific format as a regular expression which the
  field value must satisfy.<br>
  For example, an initials field which accepts 3 uppercase letters or 2 uppercase letters separated
  by a hypthen could utilise the action tag as follows:<br>
  `@REGEX="^[A-Z][A-Z-][A-Z]$"`<br>

**Please note:**
* The *@REGEX* tag will only be available if it has been enabled in the module system settings.
* The *@VALIDATE-LOGIC* and *@REGEX* action tags can be used simultaneously. A validation error will
  result if either the logic or regular expression condition fails.
* Blank fields will not be validated by these action tags. Set fields as *required* to prohibit
  blank values.

## Calculated default values

Use the **@DEFAULT-CALC** action tag to provide a field with the result of a calculation as a
default value.

This action tag can be used in the same manner as the *@CALCTEXT* action tag but will populate the
field similarly to the *@DEFAULT* action tag instead of creating a calculated field.

**Please note:** The *@DEFAULT-CALC* action tag will only be available if it has been enabled in the
module system settings.

## Random numbers

Use the **@RANDOMNUMBER** action tag to provide a field with a random number as a default value.

The random numbers generated are cryptographically secure.

The random number will be a decimal value between 0 and 1, unless used in the format
`@RANDOMNUMBER(x,y)`, where `x` and `y` are two numbers, in which case the number returned will be
an integer between the two numbers (inclusive).

**Please note:** The *@RANDOMNUMBER* action tag will only be available if it has been enabled in the
module system settings.

## Require field validation to pass on form submission

This option can be enabled in the module project settings. If enabled, this will check that when
forms are submitted with status set to complete, that there are no field validation errors and no
empty required fields. Submission will be blocked if there is a validation error or an empty
required field.

### Exempt specific forms from validation enforcement

If field validation is required to pass on form submission, this option allows some forms to be
exempted from the validation enforcement. Each form selected can be exempted from enforcement of
all validation, validation of required fields, or validation of values (format validation).

## Don't allow entry of dates in the future by default

This option can be enabled in the module project settings. If enabled, this will raise a field
validation error on any date or datetime field if a date/time after the current date/time is
entered. This can be overridden on a per-field basis with the **@ALLOWFUTURE** action tag.

## Provide option on surveys to continue regardless of whether required fields are complete
This option can be enabled in the module project settings. If enabled, survey respondants will be
provided with an option to *continue anyway* if there are required fields which have not been
completed. This will, following a warning, allow them to proceed despite the survey not being
complete.

### Exempt specific forms from survey continue option
If the option to allow a survey submission despite incomplete required fields is selected, this
option allows some forms to be exempted from this.
