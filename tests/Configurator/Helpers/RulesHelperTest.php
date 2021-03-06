<?php

namespace s9e\TextFormatter\Tests\Configurator\Helpers;

use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\Configurator\Collections\Ruleset;
use s9e\TextFormatter\Configurator\Collections\TagCollection;
use s9e\TextFormatter\Configurator\Helpers\RulesHelper;

/**
* @covers s9e\TextFormatter\Configurator\Helpers\RulesHelper
*/
class RulesHelperTest extends Test
{
	/**
	* @testdox Works with a single tag
	*/
	public function testSingleTag()
	{
		$tags = new TagCollection;

		$tags->add('A')->rules->allowChild('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox If no rule is defined, the default is to allow children and descendants
	*/
	public function testDefaultIsAllow()
	{
		$tags = new TagCollection;

		$tags->add('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox Correctly applies denyChild
	*/
	public function testTwoTags()
	{
		$tags = new TagCollection;

		$tags->add('A');
		$tags->add('B')->rules->denyChild('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x03",
					'allowedDescendants' => "\x03"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x03",
						'allowedDescendants' => "\x03"
					),
					'B' => array(
						'bitNumber'          => 1,
						'allowedChildren'    => "\x02",
						'allowedDescendants' => "\x03"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox defaultChildRule is correctly applied
	*/
	public function testDefaultChildRuleIsApplied()
	{
		$tags = new TagCollection;

		$tags->add('A')->rules->defaultChildRule('deny');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox allowChild overrides defaultChildRule('deny')
	*/
	public function testAllowChildOverridesDefaultChildRuleDeny()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->defaultChildRule('deny');
		$tag->rules->allowChild('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox denyChild overrides defaultChildRule('allow')
	*/
	public function testDenyChildOverridesDefaultChildRuleAllow()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->defaultChildRule('allow');
		$tag->rules->denyChild('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox denyChild overrides allowChild
	*/
	public function testDenyChildOverridesAllowChild()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->denyChild('A');
		$tag->rules->allowChild('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox denyDescendant overrides allowChild
	*/
	public function testDenyDescendantOverridesAllowChild()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->denyDescendant('A');
		$tag->rules->allowChild('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x00"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox defaultDescendantRule is correctly applied
	*/
	public function testDefaultDescendantRuleIsApplied()
	{
		$tags = new TagCollection;

		$tags->add('A')->rules->defaultDescendantRule('deny');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x00"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox allowDescendant overrides defaultDescendantRule('deny')
	*/
	public function testAllowDescendantOverridesDefaultDescendantRuleDeny()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->defaultDescendantRule('deny');
		$tag->rules->allowDescendant('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox allowDescendant does not override denyChild
	*/
	public function testAllowDescendantDoesNotOverrideDenyChild()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->denyChild('A');
		$tag->rules->allowDescendant('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox denyDescendant overrides defaultDescendantRule('allow')
	*/
	public function testDenyDescendantOverridesDefaultDescendantRuleAllow()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->defaultDescendantRule('allow');
		$tag->rules->denyDescendant('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x00"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox denyDescendant overrides allowDescendant
	*/
	public function testDenyDescendantOverridesAllowDescendant()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->denyDescendant('A');
		$tag->rules->allowDescendant('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x00"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox denyAll (true) overrides everything
	*/
	public function testDenyAllPositive()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->allowChild('A');
		$tag->rules->allowDescendant('A');
		$tag->rules->defaultChildRule('allow');
		$tag->rules->defaultDescendantRule('allow');
		$tag->rules->denyAll(true);

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x00",
						'allowedDescendants' => "\x00"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox denyAll (false) has no effect
	*/
	public function testDenyAllNegative()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->allowChild('A');
		$tag->rules->allowDescendant('A');
		$tag->rules->defaultChildRule('allow');
		$tag->rules->defaultDescendantRule('allow');
		$tag->rules->denyAll(false);

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox Tags with a requireParent rule are not allowed at the root
	*/
	public function testRequireParentDisallowAtRoot()
	{
		$tags = new TagCollection;

		$tags->add('A');
		$tags->add('B')->rules->requireParent('A');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x03"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x03",
						'allowedDescendants' => "\x03"
					),
					'B' => array(
						'bitNumber'          => 1,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x03"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox Tags that aren't allowed anywhere are omitted from the return array
	*/
	public function testUnusedTag()
	{
		$tags = new TagCollection;
		$tags->add('A')->rules->denyChild('B');
		$tags->add('B');

		$rootRules = new Ruleset;
		$rootRules->denyChild('B');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, $rootRules)
		);
	}

	/**
	* @testdox Tags that are allowed in a closed dependency loop are omitted from the return array
	*/
	public function testUnusedTagsInLoop()
	{
		$tags = new TagCollection;
		$tags->add('A');
		$tags->add('B');

		$rootRules = new Ruleset;
		$rootRules->denyChild('A');
		$rootRules->denyChild('B');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x00",
					'allowedDescendants' => "\x00"
				),
				'tags' => array()
			),
			RulesHelper::getBitfields($tags, $rootRules)
		);
	}

	/**
	* @testdox Rules targeting inexistent tags do not interfere
	*/
	public function testInexistentTag()
	{
		$tags = new TagCollection;

		$tag = $tags->add('A');
		$tag->rules->allowChild('C');
		$tag->rules->allowDescendant('C');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}

	/**
	* @testdox Bitfields are compressed by making tags that are targeted by the same permissions share the same bit number
	*/
	public function testTwoIdenticalTags()
	{
		$tags = new TagCollection;

		$tags->add('A');
		$tags->add('B');

		$this->assertEquals(
			array(
				'root' => array(
					'allowedChildren'    => "\x01",
					'allowedDescendants' => "\x01"
				),
				'tags' => array(
					'A' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					),
					'B' => array(
						'bitNumber'          => 0,
						'allowedChildren'    => "\x01",
						'allowedDescendants' => "\x01"
					)
				)
			),
			RulesHelper::getBitfields($tags, new Ruleset)
		);
	}
}