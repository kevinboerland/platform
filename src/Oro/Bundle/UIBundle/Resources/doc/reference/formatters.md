Formatters
=================

Overview
--------

Formatters is the set of filters what can be assigned to some data. 


Formatters implementation
-------------------------

To create own formatter, programmer should create new service that should be tagged with `oro_formatter` tag.

This tag has the following attributes:

* **formatter** - The formatter name. It is mandatory attribute.
* **data_type** - The data type name for which the formatter should be used by default.

Example:
  
```
      acme_demo.formatter.some_formatter:
          class: Acme\Bundle\AcmeBundle\Formatter\SomeFormatter
          tags:
            - { name: oro_formatter, formatter: some_formatter }    
```

The service class should implements `Oro\Bundle\UIBundle\Formatter\FormatterInterface` interface.


Formatters usage
----------------


To apply some formatter, you can use `oro_ui.formatter` service.

This manager have method `format` what applies given formatter to the parameter:

```
...
use Oro\Bundle\UIBundle\Formatter\FormatterManager;

...
/** @var FormatterManager **/
protected $formatterManager
...

$date = new \DateTime();
$formattedValue = $this->formatterManager->format($date, 'datetime');

```

In this example, formatter `datetime` applyies to the $date variable.


To use formatters from the twig templates, you can use `oro_format` filter:

```
    {{ datetimeVar|oro_format('datetime') }}
```
