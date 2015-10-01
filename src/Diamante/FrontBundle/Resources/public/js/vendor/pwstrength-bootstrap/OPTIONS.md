# Options

The plugin expect the options to follow this structure:

```javascript
options = {
    common: {},
    rules: {},
    ui: {}
};
```

Let's see the options of each section.

## Common

* __minChar__:

  Default: `6` (Integer)

  Sets the minimum required of characters for a password to not be considered
  too weak.

* __usernameField__:

  Default: `"#username"` (String)

  The username field to match a password to, to ensure the user does not use
  the same value for their password.

* __userInputs__:

  Default: `[]` (Array)

  Array of CSS selectors for input fields with user input. The content of these
  fields will be retrieved and passed to the zxcvbn library.

  This option only takes effect when the zxcvbn library is being used. Check
  the `zxcvbn` option.

* __onLoad__:

  Default: `undefined` (Function)

  A callback function, fired on load of the widget. No arguments will be
  passed.

* __onKeyUp__:

  Default: `undefined` (Function)

  A callback function, fired on key up when the user is typing. The `keyup`
  event will be passed as first argument, and an object as second with the
  score and the verdict text and level.

  This handler will also be called when the `change` or the `onpaste` events
  happen.

* __zxcvbn__:

  Default: `false` (Boolean)

  Use the zxcvbn to calculate the password entropy and use it as the score. For
  more information about zxcvbn plase check this
  [post](https://tech.dropbox.com/2012/04/zxcvbn-realistic-password-strength-estimation/).

  If you activate this setting, then all the rules won't be applied, and all
  the options under the _Rules_ section will be ignored as well. zxcvbn will be
  used instead of the default rules engine.

  To use this option you must load the zxcvbn library file on your site. You'll
  find it at their [repository](https://github.com/lowe/zxcvbn).

* __zxcvbnTerms__:

  Default: `[]` (Array)

  An array of strings. A list of banned words for the zxcvbn library. This
  option will be ignored if zxcvbn is not being used.

* __debug__:

  Default: `false` (Boolean)

  If true, it prints the password strength in the javascript console, so you
  can test and tune your rules settings.

## Rules

* __extra__:

  Default: `{}` (Object)

  Empty object where custom rules functions will be stored.

* __scores__:

  Default: (Object)

  ```
  {
    wordNotEmail: -100,
    wordLength: -50,
    wordSimilarToUsername: -100,
    wordSequences: -50,
    wordTwoCharacterClasses: 2,
    wordRepetitions: -25,
    wordLowercase: 1,
    wordUppercase: 3,
    wordOneNumber: 3,
    wordThreeNumbers: 5,
    wordOneSpecialChar: 3,
    wordTwoSpecialChar: 5,
    wordUpperLowerCombo: 2,
    wordLetterNumberCombo: 2,
    wordLetterNumberCharCombo: 2
  }
  ```

  Scores returned by the rules when they match. Negative values are for
  penalizations.

* __activated__:

  Default: (Object)

  ```
  {
    wordNotEmail: true,
    wordLength: true,
    wordSimilarToUsername: true,
    wordSequences: true,
    wordTwoCharacterClasses: false,
    wordRepetitions: false,
    wordLowercase: true,
    wordUppercase: true,
    wordOneNumber: true,
    wordThreeNumbers: true,
    wordOneSpecialChar: true,
    wordTwoSpecialChar: true,
    wordUpperLowerCombo: true,
    wordLetterNumberCombo: true,
    wordLetterNumberCharCombo: true
  }
  ```
  An object that sets wich validation rules are activated. By changing this
  object is possible to deactivate some validations, or to activate them for
  extra security.

* __raisePower__:

  Default: `1.4` (Double)

  The value used to modify the final score, based on the password length,
  allows you to tailor your results.

## User Interface

* __bootstrap2__:

  Default: `false` (Boolean)

  Sets if it supports legacy Bootstrap 2 (true) or the current Bootstrap 3
  (false), the progress bar html is different.

* __showProgressBar__:

  Default: `true` (Boolean)

  Displays the password strength in a progress bar.

* __showPopover__:

  Default: `false` (Boolean)

  Displays the error messages and the verdicts in a Bootstrap popover, instead
  of below the input field. Bootstrap tooltip.js and popover.js must to be
  included.

  If the `showVerdictsInsideProgressBar` option is active, then the verdicts
  won't appear on the popover.

* __showStatus__:

  Default: `false` (Boolean)

  Displays the password strength as a validation status in the password field,
  for this to work, the Bootstrap form structure must be followed.

* __spanError__:

  Default: (Function)

  ```javascript
  function (options, key) {
    var text = options.ui.errorMessages[key];
    return '<span style="color: #d52929">' + text + '</span>';
  };
  ```

  Function to generate a span with the style property set to red font color,
  used in the errors messages. Overwrite for custom styling.

* __popoverError__:

  Default: (Function)

  ```javascript
  function (errors) {
    var message = "<div>Errors:<ul class='error-list' style='margin-bottom: 0;'>";
    jQuery.each(errors, function (idx, err) { message += "<li>" + err + "</li>"; });
    message += "</ul></div>";
    return message;
  };
  ```

  Function to generate the errors list in the popover if `showPopover` is true.
  Overwrite for custom styling.

* __errorMessages__:

  Default: (Object)

  ```
  {
    wordLength: "Your password is too short",
    wordNotEmail: "Do not use your email as your password",
    wordSimilarToUsername: "Your password cannot contain your username",
    wordTwoCharacterClasses: "Use different character classes",
    wordRepetitions: "Too many repetitions",
    wordSequences: "Your password contains sequences"
  }
  ```

  An object containing error messages. These can be overwritten for language
  purposes, and extra messages can also be added for other rules, existing or
  custom. Use the name of the rule as key.

* __verdicts__:

  Default: `["Weak", "Normal", "Medium", "Strong", "Very Strong"]` (Array)

  The display names for the verdicts related to the progressClass. It has to
  have 5 elements, because there are 5 possible strength categories. It can be
  html code.

* __showVerdicts__:

  Default: `true` (Boolean)

  Determines if the verdicts are displayed or not.

* __showVerdictsInsideProgressBar__:

  Default: `false` (Boolean)

  Determines if the verdicts are displayed inside the progress bar or not. When
  this setting is active, the verdict viewport is ignored and they won't appear
  on the popover if it is being showed. Also this option overrides the value of
  the _showVerdicts_ one.

* __useVerdictCssClass__:

  Default: `false` (Boolean)

  Determines if it's necessary to add a css class in the verdict element.

* __showErrors__:

  Default: `false` (Boolean)

  Determines if the error list is displayed with the progress bar or not.

* __container__:

  Default: `undefined` (CSS selector, or DOM node)

  If defined, it will be used to locate the viewports, if undefined, the parent
  of the input password will be used instead. The viewports must be children of
  this node.

* __viewports__:

  Default: (Object)

  ```
  {
    progress: undefined,
    verdict: undefined,
    errors: undefined
  }
  ```

  An object containing the viewports to use to show the elements of the
  strength meter. Each one can be a CSS selector (`"#progressbar"`) or a DOM
  node reference.

* __scores__:

  Default: `[17, 26, 40, 50]` (Array)

  The scores used to determine what progressClass and verdicts to display. It
  has to have 4 elements, which creates 5 categories of strength (the 5
  possible verdicts).

### Example of an options object

```javascript
var options = {};
options.common = {
    minChar: 8;
};
options.rules = {
    activated: {
        wordTwoCharacterClasses: true,
        wordRepetitions: true
    }
};
options.ui = {
    showErrors: true
};
```
