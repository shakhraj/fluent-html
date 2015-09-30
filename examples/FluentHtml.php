<?php
require __DIR__ . '/../vendor/autoload.php';

use FewAgency\FluentHtml\FluentHtml;

echo "\n";

// Simple example
echo FluentHtml::create('div')->withClass('wrapper')
    ->containingElement('p')->withAttribute('id', 'p1')->withContent('This is a paragraph.', 'It has two sentences.')
    ->followedByElement('p')->withAttribute('id', 'p2')->withContent('This is another paragraph.');

echo "\n\n";

// Example with conditions
$show_div = $show_2nd_sentence = $p2_id = false;

echo FluentHtml::create(function () use ($show_div) {
    if ($show_div) {
        return 'div';
    }
})->withClass('wrapper')
    ->containingElement('p')->withAttribute('id', function () {
        return 'p1';
    })->withContent(['This is a paragraph.', 'It may have two sentences.' => $show_2nd_sentence])
    ->followedByElement('p')->withAttribute('id', $p2_id)->withContent(function () {
        return 'This is another paragraph.';
    });

echo "\n\n";

// Bootstrap form-group
$name = 'username';
$value = 'test@test.com';
$control_id = $name;
$control_help_id = "{$control_id}_help";
// These options can be removed or set and that controls the final rendering
$errors[$name] = ["{$name} is required", "{$name} must be a valid email address"];
$help_text = "{$name} is your email address";
$input_group_prepend = new \Illuminate\Support\HtmlString(
    '<span class="input-group-addon"><input type="checkbox" aria-label="Addon checkbox"></span>'
);
$input_group_append = new \Illuminate\Support\HtmlString(
    '<span class="input-group-btn"><button class="btn btn-default" type="button">Go!</button></span>'
);

// Build the input's help (aria-describedby) element and keep a reference
$control_help = FluentHtml::create('div')->withAttribute('id', $control_help_id)->onlyDisplayedIfHasContent();

// Add any errors relevant to the input as a list in the help element
$control_help->containingElement('ul')->withClass('help-block', 'list-unstyled')->onlyDisplayedIfHasContent()
    ->withContentWrappedIn($errors[$name], 'li', ['class' => 'text-capitalize-first'])
    // Put the fixed message at the end of the help element
    ->followedByElement('span', $help_text)->withClass('help-block')->onlyDisplayedIfHasContent();

// Build the input element and keep a reference
$input = FluentHtml::create('input')->withAttribute('type', 'text')->withClass('form-control')
    ->withAttribute(['name' => $name, 'value' => $value, 'id' => $control_id, 'readonly'])
    ->withAttribute('aria-describedby', function () use ($control_help) {
        // Only set the input's aria-describedby attribute if the help element has any content
        if ($control_help->hasContent()) {
            return $control_help->getAttribute('id');
        }
    });

// Build the input-group
$input_group = $input->siblingsWrappedInElement(function ($input_group) {
    // Print the input-group tag only when there's at least one input group addon next to the input
    return $input_group->getContentCount() > 1 ? 'div' : false;
})->withClass('input-group')
    //Add the input group addons if they are set
    ->withPrependedContent($input_group_prepend)->withAppendedContent($input_group_append);

// Wrap up and print the full result from here
echo $input_group
    // Add a label before the input-group, defaulting to the input name if label not specified
    ->precededByElement('label', empty($label) ? $name : $label)->withClass('control-label')
    ->withAttribute('for', function () use ($input) {
        return $input->getAttribute('id');
    })
    // Wrap the label and input-group in a form-group
    ->siblingsWrappedInElement('div')->withClass('form-group')
    ->withClass(function () use ($errors, $name) {
        // Set the validation state class on the form-group
        if (count($errors[$name])) {
            return 'has-error';
        }
    })
    // Add the help element last in the form-group
    ->withAppendedContent($control_help);

echo "\n\n";