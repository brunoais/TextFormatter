<?php

namespace s9e\Toolkit\Tests\TextFormatter\Plugins;

use s9e\Toolkit\Tests\Test,
	s9e\Toolkit\TextFormatter\ConfigBuilder;

include_once __DIR__ . '/../../Test.php';

/**
* @covers s9e\Toolkit\TextFormatter\Plugins\BBCodesConfig
*/
class BBCodesConfigTest extends Test
{
	/**
	* @test
	*/
	public function getConfig_returns_false_if_no_BBCodes_were_added()
	{
		$this->assertFalse($this->cb->BBCodes->getConfig());
	}

	/**
	* @test
	*/
	public function A_single_asterisk_is_accepted_as_a_BBCode_name()
	{
		$this->assertTrue($this->cb->BBCodes->isValidBBCodeName('*'));
	}

	/**
	* @test
	*/
	public function An_asterisk_followed_by_anything_is_rejected_as_a_BBCode_name()
	{
		$this->assertFalse($this->cb->BBCodes->isValidBBCodeName('**'));
		$this->assertFalse($this->cb->BBCodes->isValidBBCodeName('*b'));
	}

	/**
	* @test
	*/
	public function BBCode_names_can_start_with_a_letter()
	{
		$this->assertTrue($this->cb->BBCodes->isValidBBCodeName('a'));
	}

	/**
	* @test
	*/
	public function BBCode_names_cannot_start_with_anything_else()
	{
		$allowedChars    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz*';
		$disallowedChars = count_chars($allowedChars, 4);

		foreach (str_split($disallowedChars, 1) as $c)
		{
			$this->assertFalse($this->cb->BBCodes->isValidBBCodeName($c));
		}
	}

	/**
	* @test
	*/
	public function BBCode_names_can_only_contain_letters_numbers_and_underscores()
	{
		$allowedChars    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';
		$disallowedChars = count_chars($allowedChars, 4);

		foreach (str_split($disallowedChars, 1) as $c)
		{
			$this->assertFalse($this->cb->BBCodes->isValidBBCodeName('A' . $c));
		}
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid BBCode name ']'
	*/
	public function addBBCode_rejects_invalid_BBCode_names()
	{
		$this->cb->BBCodes->addBBCode(']');
	}

	/**
	* @test
	*/
	public function BBCodes_are_mapped_to_a_tag_of_the_same_name_by_default()
	{
		$this->cb->BBCodes->addBBCode('B');

		$parserConfig = $this->cb->getParserConfig();

		$this->assertArrayHasKey('B', $parserConfig['tags']);
		$this->assertSame(
			'B', $parserConfig['plugins']['BBCodes']['bbcodesConfig']['B']['tagName']
		);
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage BBCode 'A' already exists
	*/
	public function addBBCode_throws_an_exception_if_the_BBCode_name_is_already_in_use()
	{
		$this->cb->BBCodes->addBBCode('A');
		$this->cb->BBCodes->addBBCode('A');
	}

	/**
	* @test
	*/
	public function A_BBCode_can_map_to_a_tag_of_a_different_name()
	{
		$this->cb->BBCodes->addBBCode('A', array('tagName' => 'B'));
		$this->assertTrue($this->cb->tagExists('B'));
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Tag 'A' does not exist
	*/
	public function addBBCodeAlias_throws_an_exception_if_the_tag_does_not_exist()
	{
		$this->cb->BBCodes->addBBCodeAlias('A', 'A');
	}

	/**
	* @test
	* @depend BBCodes_are_mapped_to_a_tag_of_the_same_name_by_default
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage BBCode 'A' already exists
	*/
	public function addBBCodeAlias_throws_an_exception_if_the_BBCode_already_exists()
	{
		$this->cb->BBCodes->addBBCode('A');
		$this->cb->BBCodes->addBBCodeAlias('A', 'A');
	}

	/**
	* @test
	* @depend BBCodes_are_mapped_to_a_tag_of_the_same_name_by_default
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid tag name '*'
	*/
	public function addBBCodeAlias_cannot_create_an_alias_to_an_invalid_tag_name()
	{
		$this->cb->BBCodes->addBBCodeAlias('A', '*');
	}

	/**
	* @test
	*/
	public function Can_tell_whether_a_BBCode_exists()
	{
		$this->assertFalse($this->cb->BBCodes->bbcodeExists('A'));
		$this->cb->BBCodes->addBBCode('A');
		$this->assertTrue($this->cb->BBCodes->bbcodeExists('A'));
	}

	/**
	* @test
	* @depends BBCodes_are_mapped_to_a_tag_of_the_same_name_by_default
	*/
	public function Can_return_all_options_of_a_BBCode()
	{
		$this->cb->BBCodes->addBBCode('A');

		$this->assertArrayMatches(
			array('tagName' => 'A'),
			$this->cb->BBCodes->getBBCodeOptions('A')
		);
	}

	/**
	* @test
	* @depends BBCodes_are_mapped_to_a_tag_of_the_same_name_by_default
	*/
	public function Can_return_the_value_of_an_option_of_a_BBCode()
	{
		$this->cb->BBCodes->addBBCode('A');
		$this->assertSame('A', $this->cb->BBCodes->getBBCodeOption('A', 'tagName'));
	}

	/**
	* @test
	* @depends Can_return_the_value_of_an_option_of_a_BBCode
	*/
	public function Can_return_the_value_of_an_option_of_a_BBCode_even_if_it_is_null()
	{
		$this->cb->BBCodes->addBBCode('A', array('autoClose' => null));
		$this->assertNull($this->cb->BBCodes->getBBCodeOption('A', 'autoClose'));
	}

	/**
	* @test
	* @depends Can_return_all_options_of_a_BBCode
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage BBCode 'A' does not exist
	*/
	public function getBBCodeOptions_throws_an_exception_if_the_BBCode_does_not_exist()
	{
		$this->cb->BBCodes->getBBCodeOptions('A');
	}

	/**
	* @test
	* @depends Can_return_the_value_of_an_option_of_a_BBCode
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage BBCode 'A' does not exist
	*/
	public function getBBCodeOption_throws_an_exception_if_the_BBCode_does_not_exist()
	{
		$this->cb->BBCodes->getBBCodeOption('A', 'tagName');
	}

	/**
	* @test
	* @depends Can_return_the_value_of_an_option_of_a_BBCode
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Unknown option 'XYZ' from BBCode 'A'
	*/
	public function getBBCodeOption_throws_an_exception_if_the_option_does_not_exist()
	{
		$this->cb->BBCodes->addBBCode('A');
		$this->cb->BBCodes->getBBCodeOption('A', 'XYZ');
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid attribute name '**'
	*/
	public function setBBCodeOption_cannot_set_a_defaultAttr_with_an_invalid_name()
	{
		$this->cb->BBCodes->addBBCode('A');
		$this->cb->BBCodes->setBBCodeOption('A', 'defaultAttr', '**');
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid attribute name '**'
	*/
	public function setBBCodeOption_cannot_set_a_contentAttr_with_an_invalid_name()
	{
		$this->cb->BBCodes->addBBCode('A');
		$this->cb->BBCodes->setBBCodeOption('A', 'contentAttr', '**');
	}

	/**
	* @test
	* @depends Can_tell_whether_a_BBCode_exists
	*/
	public function addBBCodeFromExample_works_on_simple_BBCodes()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[B]{TEXT}[/B]', '<b>{TEXT}</b>');
		$this->assertTrue($this->cb->BBCodes->BBCodeExists('B'));
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Cannot interpret the BBCode definition
	*/
	public function addBBCodeFromExample_throws_an_exception_if_the_definition_is_malformed()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[foo==]{TEXT}[/foo]', '');
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Invalid XML in template - error was: Premature end of data
	*/
	public function addBBCodeFromExample_throws_an_exception_if_the_template_is_not_wellformed_XML()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[HR][/HR]', '<hr>');
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Undefined placeholder {ID} found in template
	*/
	public function addBBCodeFromExample_throws_an_exception_if_an_undefined_placeholder_is_found_in_an_attribute()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[B][/B]', '<b id="{ID}"></b>');
	}

	/**
	* @test
	* @expectedException InvalidArgumentException
	* @expectedExceptionMessage Undefined placeholder {TEXT} found in template
	*/
	public function addBBCodeFromExample_throws_an_exception_if_an_undefined_placeholder_is_found_anywhere()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[B][/B]', '<b>{TEXT}{</b>');
	}

	/**
	* @test
	* @expectedException RuntimeException
	* @expectedExceptionMessage ALLOW_INSECURE_TEMPLATES
	*/
	public function addBBCodeFromExample_throws_an_exception_if_a_TEXT_placeholder_is_found_in_an_attribute()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[B={TEXT}][/B]', '<b id="{TEXT}"></b>');
	}

	/**
	* @test
	*/
	public function addBBCodeFromExample_does_not_throw_an_exception_if_a_TEXT_placeholder_is_found_in_an_attribute_but_ALLOW_INSECURE_TEMPLATES_flag_is_set()
	{
		$this->cb->BBCodes->addBBCodeFromExample(
			'[B={TEXT}][/B]',
			'<b id="{TEXT}"></b>',
			ConfigBuilder::ALLOW_INSECURE_TEMPLATES
		);
	}

	/**
	* @test
	* @depends addBBCodeFromExample_works_on_simple_BBCodes
	* @depends Can_return_the_value_of_an_option_of_a_BBCode
	*/
	public function addBBCodeFromExample_allows_a_single_start_tag_with_no_end_tag_and_enables_autoClose()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[HR]', '<hr />');
		$this->assertTrue($this->cb->BBCodes->BBCodeExists('HR'));
		$this->assertTrue($this->cb->BBCodes->getBBCodeOption('HR', 'autoClose'));
	}

	/**
	* @test
	* @depends addBBCodeFromExample_works_on_simple_BBCodes
	* @depends Can_return_the_value_of_an_option_of_a_BBCode
	*/
	public function addBBCodeFromExample_allows_a_self_closed_tag_and_enables_autoClose()
	{
		$this->cb->BBCodes->addBBCodeFromExample('[HR /]', '<hr />');
		$this->assertTrue($this->cb->BBCodes->BBCodeExists('HR'));
		$this->assertTrue($this->cb->BBCodes->getBBCodeOption('HR', 'autoClose'));
	}

	/**
	* @test
	* @depends addBBCodeFromExample_works_on_simple_BBCodes
	* @depends Can_return_the_value_of_an_option_of_a_BBCode
	*/
	public function addBBCodeFromExample_handles_default_attribute_and_gives_it_the_same_name_as_the_tag()
	{
		$this->cb->BBCodes->addBBCodeFromExample(
			'[A={URL}]{TEXT}[/A]',
			'<a href="{URL}">{TEXT}</a>'
		);

		$this->assertTrue($this->cb->BBCodes->BBCodeExists('A'));
		$this->assertSame('a', $this->cb->BBCodes->getBBCodeOption('A', 'defaultAttr'));

		$this->assertTrue($this->cb->attributeExists('A', 'a'));
		$this->assertSame('url', $this->cb->getTagAttributeOption('A', 'a', 'type'));
	}
}