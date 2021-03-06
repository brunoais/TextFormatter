Rules
=====

See `s9e\TextFormatter\Configurator\Collections\Ruleset`.
Rules are set on a per-tag basis, for example:

```
$configurator = new Configurator;

$tag = $configurator->tags->add('B');
$tag->rules->autoReopen();
$tag->rules->defaultChildRule('allow');
$tag->rules->denyChild('X');
```

Rules can be:

 * boolean -- they accept `true` or `false` as argument, with `true` being the default
 * targeted -- they accept a tag name as argument
 * other -- `defaultChildRule()` and `defaultDescendantRule()` accept either `"allow"` or `"deny"`

Rules that apply to descendants also apply to children. Rules that apply to ancestors also apply to the parent. A tag that is explicitly denied cannot be allowed by another rule.

<dl>

<dt>allowChild</dt>
<dd><i>Example:</i> <code>$tag->rules->allowChild('X');</code><br/>
Allows tag X to be used as a child of given tag.</dd>

<dt>allowDescendant</dt>
<dd><i>Example:</i> <code>$tag->rules->allowDescendant('X');</code><br/>
Allows tag X to be used as a descendant of given tag.</dd>

<dt>autoClose</dt>
<dd><i>Example:</i> <code>$tag->rules->autoClose(true);</code><br/>
Start tags of this tag are automatically closed if they are not paired with an end tag. This rule exists primarily to deal with <a href="http://www.w3.org/html/wg/drafts/html/master/single-page.html#void-elements">void elements</a> such as `<img>`.</dd>

<dt>autoReopen</dt>
<dd><i>Example:</i> <code>$tag->rules->autoReopen(false);</code><br/>
Automatically reopens this tag if it's closed by a non-matching tag. This rule helps dealing with misnested tags such as `<B><I></B></I>`. In this case, if `I` has an autoReopen rule, it will automatically be reopen when `B` closes.</dd>

<dt>closeAncestor</dt>
<dd><i>Example:</i> <code>$tag->rules->closeAncestor('X');</code><br/>
Forces all ancestor tags X to be closed when this tag is encountered.</dd>

<dt>closeParent</dt>
<dd><i>Example:</i> <code>$tag->rules->closeParent('LI');</code><br/>
Forces current parent LI to be closed when this tag is encountered. Helps dealing with <a href="http://www.w3.org/html/wg/drafts/html/master/single-page.html#optional-tags">optional end tags</a>. For instance, if LI has a closeParent rule targeting LI, the following `<LI>one<LI>two` is interpreted as `<LI>one</LI><LI>two`.</dd>

<dt>defaultChildRule</dt>
<dd><i>Example:</i> <code>$tag->rules->defaultChildRule('deny');</code><br/>
If defaultChildRule is set to 'deny', all tags that are not targeted by an allowChild rule will be denied. By default, defaultChildRule is set to 'deny', which means that all tags are allowed as children unless they are targeted by a denyChild rule. </dd>

<dt>defaultDescendantRule</dt>
<dd><i>Example:</i> <code>$tag->rules->defaultDescendantRule('deny');</code><br/>
Same as defaultChildRule but with descendants.</dd>

<dt>denyAll</dt>
<dd><i>Example:</i> <code>$tag->rules->denyAll();</code><br/>
Prevents this tag from having any child.</dd>

<dt>denyChild</dt>
<dd><i>Example:</i> <code>$tag->rules->denyChild('X');</code><br/>
Prevents tag X to be used as a child of this tag.</dd>

<dt>denyDescendant</dt>
<dd><i>Example:</i> <code>$tag->rules->denyDescendant('X');</code><br/>
Prevents tag X to be used as a descendant of this tag.</dd>

<dt>ignoreText</dt>
<dd><i>Example:</i> <code>$tag->rules->ignoreText();</code><br/>
Prevents plain text from being displayed as a child of this tag. Also disables line breaks. This rule deals with elements that do not allow text, such as lists. Does not apply to descendants.</dd>

<dt>isTransparent</dt>
<dd><i>Example:</i> <code>$tag->rules->isTransparent();</code><br/>
Indicates that this tag uses the <a href="http://www.w3.org/html/wg/drafts/html/master/single-page.html#transparent-content-models">transparent content model</a> and their allow/deny rules are inherited from its parent.</dd>

<dt>noBrChild</dt>
<dd><i>Example:</i> <code>$tag->rules->noBrChild();</code><br/>
Prevents newlines in child text nodes from being converted to `<br/>`.</dd>

<dt>noBrDescendant</dt>
<dd><i>Example:</i> <code>$tag->rules->noBrDescendant();</code><br/>
Prevents newlines in descendant text nodes from being converted to `<br/>`. Useful for elements that preserves whitespace.</dd>

<dt>requireParent</dt>
<dd><i>Example:</i> <code>$tag->rules->requireParent('X');</code><br/>
Prevents this tag from being used unless it's as a child of X. If multiple requireParent rules are set, only one has to be satisfied.</dd>

<dt>requireAncestor</dt>
<dd><i>Example:</i> <code>$tag->rules->requireAncestor('X');</code><br/>
Prevents this tag from being used unless it's as a descendant of X. If multiple requireDescendant rules are set, all of them must be satisfied.</dd>

<dt>trimWhitespace</dt>
<dd><i>Example:</i> <code>$tag->rules->trimWhitespace(false);</code><br/>
Whether whitespace around this tag should be ignored. Useful for allowing whitespace around block elements without extra newlines being displayed. Limited to 2 newlines before and after the tag and 1 newline at the start and at the end of its content.</dd>

</dl>