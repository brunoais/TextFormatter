#!/usr/bin/php
<?php

$filters = array(
	'int'     => '1010100000000',
	'uint'    => '1010000000000',
	'number'  => '1011000100000',
	'float'   => '1011111111110'
);

$values = array(
	'strings made entirely of digits'
		=> '123',
	'strings that starts with digits'
		=> '123abc',
	'integers'
		=> 123,
	'numbers that start with a zero'
		=> ['0123', '123'],
	'negative numbers'
		=> '-123',
	'decimal numbers'
		=> '12.3',
	'floats'
		=> 12.3,
	'numbers too big for the PHP integer type'
		=> '10000000000000000000',
	'positive numbers in E notation'
		=> ['12e3', '12000'],
	'negative numbers in E notation'
		=> ['-12e3', '-12000'],
	'positive numbers in E notation with a negative exponent'
		=> ['12e-3', '0.012'],
	'negative numbers in E notation with a negative exponent'
		=> ['-12e-3', '-0.012'],
	'numbers in hex notation'
		=> '0x123',
);

$php = '';

foreach ($filters as $filter => $mask)
{
	$i = 0;
	foreach ($values as $name => $value)
	{
		if (is_array($value))
		{
			list($original, $expected) = $value;
		}
		else
		{
			$original = $expected = $value;
		}

		$testdox = 'Filter "' . $filter . '"'
		         . (($mask[$i]) ? ' accepts ' : ' rejects ')
		         . $name;

		$testName = 'testFilter' . sprintf('%08X', crc32($testdox));

		$php .= "\n\t/** @testdox " . $testdox . ' */'
		      . "\n\tpublic function " . $testName . '() { $this->assertFilterValueIs'
		      . (($mask[$i]) ? 'Valid' : 'Invalid')
		      . '('
		      . var_export($filter, true)
		      . ', ' . var_export($original, true)
		      . (($mask[$i] && $expected !== $original && $filter !== 'number') ? ', ' . var_export($expected, true) : '')
		      . "); }\n";

		++$i;
	}
}

$filepath = __DIR__ . '/../tests/Parser/BuiltInFiltersTest.php';
$file = file_get_contents($filepath);

$startComment = preg_quote('// Start of content generated by /scripts/' . basename(__FILE__));
$endComment = preg_quote("\t// End of content generated by /scripts/" . basename(__FILE__));

$file = preg_replace(
	'#(?<=' . $startComment . ')(.*?)(?=' . $endComment . ')#s',
	$php,
	$file
);

file_put_contents($filepath, $file);