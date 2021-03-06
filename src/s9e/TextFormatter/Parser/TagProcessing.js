/**
* @type {!Object.<string,!number>} Number of open tags for each tag name
*/
var cntOpen;

/**
* @type {!Object.<string,!number>} Number of times each tag has been used
*/
var cntTotal;

/**
* @type {!Object} Current context
*/
var context;

/**
* @type {Tag} Current tag being processed
*/
var currentTag;

/**
* @type {!Array.<!Tag>} Stack of open tags (instances of Tag)
*/
var openTags;

/**
* @type {!number} Position of the cursor in the original text
*/
var pos;

/**
* @type {!Object} Root context, used at the root of the document
*/
var rootContext;

/**
* Process all tags in the stack
*/
function processTags()
{
	// Reset some internal vars
	pos        = 0;
	cntOpen    = {};
	cntTotal   = {};
	openTags   = [];
	context    = rootContext;
	currentTag = null;

	// Initialize the count tables
	for (var tagName in tagsConfig)
	{
		cntOpen[tagName]  = 0;
		cntTotal[tagName] = 0;
	}

	while (tagStack.length)
	{
		currentTag = tagStack.pop();
		processCurrentTag();
	}

	// Close tags that were left open
	while (openTags.length)
	{
		// Get the last open tag
		var openTag = openTags[openTags.length - 1];

		// Create a tag paired to the last open tag
		var endTag = new Tag(
			Tag.END_TAG,
			openTag.getName(),
			textLen,
			0
		);
		openTag.pairWith(endTag);

		// Now process the end tag
		processEndTag(endTag);
	}

	finalizeOutput();
}

/**
* Process current tag
*/
function processCurrentTag()
{
	if (currentTag.isInvalid())
	{
		return;
	}

	var tagPos = currentTag.getPos(),
		tagLen = currentTag.getLen();

	// Test whether this tag is out of bounds
	if (tagPos + tagLen > textLen)
	{
		currentTag.invalidate();

		return;
	}

	// Test whether the cursor passed this tag's position already
	if (pos > tagPos)
	{
		// Test whether this tag is paired with a start tag and this tag is still open
		var startTag = currentTag.getStartTag();

		// TODO: IE support?
		if (startTag && openTags.indexOf(startTag) >= 0)
		{
			// Create an end tag that matches current tag's start tag, which consumes as much of
			// the same text as current tag and is paired with the same start tag
			addEndTag(
				startTag.getName(),
				pos,
				Math.max(0, tagPos + tagLen - pos)
			).pairWith(startTag);

			// Note that current tag is not invalidated, it's merely replaced
			return;
		}

		// If this is an ignore tag, try to ignore as much as the remaining text as possible
		if (currentTag.isIgnoreTag())
		{
			var ignoreLen = tagPos + tagLen - pos;

			if (ignoreLen > 0)
			{
				// Create a new ignore tag and move on
				addIgnoreTag(pos, ignoreLen);

				return;
			}
		}

		// Skipped tags are invalidated
		currentTag.invalidate();

		return;
	}

	if (currentTag.isIgnoreTag())
	{
		outputIgnoreTag(currentTag);
	}
	else if (currentTag.isBrTag())
	{
		outputBrTag(currentTag);
	}
	else if (currentTag.isStartTag())
	{
		processStartTag(currentTag);
	}
	else
	{
		processEndTag(currentTag);
	}
}

/**
* Process given start tag (including self-closing tags) at current position
*
* @param {!Tag} tag Start tag (including self-closing)
*/
function processStartTag(tag)
{
	var tagName   = tag.getName(),
		tagConfig = tagsConfig[tagName];

	// 1. Check that this tag has not reached its global limit tagLimit
	// 2. Execute this tag's filterChain, which will filter/validate its attributes
	// 3. Apply closeParent and closeAncestor rules
	// 4. Check for nestingLimit
	// 5. Apply requireAncestor rules
	//
	// This order ensures that the tag is valid and within the set limits before we attempt to
	// close parents or ancestors. We need to close ancestors before we can check for nesting
	// limits, whether this tag is allowed within current context (the context may change
	// as ancestors are closed) or whether the required ancestors are still there (they might
	// have been closed by a rule.)
	if (cntTotal[tagName] >= tagConfig.tagLimit
	 || !filterTag(tag))
	{
		// This tag is invalid
		tag.invalidate();

		return;
	}

	if (closeParent(tag) || closeAncestor(tag))
	{
		// This tag parent/ancestor needs to be closed, we just return (the tag is still valid)
		return;
	}

	if (cntOpen[tagName] >= tagConfig.nestingLimit
	 || requireAncestor(tag)
	 || !tagIsAllowed(tagName))
	{
		// This tag is invalid
		tag.invalidate();

		return;
	}

	// If this tag has an autoClose rule and it's not paired with an end tag, we replace it
	// with a self-closing tag with the same properties
	if (tagConfig.rules.flags & RULE_AUTO_CLOSE
	 && !tag.getEndTag())
	{
		var newTag = new Tag(Tag.SELF_CLOSING_TAG, tagName, tag.getPos(), tag.getLen());
		newTag.setAttributes(tag.getAttributes());

		tag = newTag;
	}

	// This tag is valid, output it and update the context
	outputTag(tag);
	pushContext(tag);
}

/**
* Process given end tag at current position
*
* @param {!Tag} tag End tag
*/
function processEndTag(tag)
{
	var tagName = tag.getName();

	if (!cntOpen[tagName])
	{
		// This is an end tag with no start tag
		return;
	}

	/**
	* @type {!Array.<!Tag>} List of tags need to be closed before given tag
	*/
	var closeTags = [];

	// Iterate through all open tags from last to first to find a match for our tag
	var i = openTags.length;
	while (--i >= 0)
	{
		var openTag = openTags[i];

		if (tag.canClose(openTag))
		{
			break;
		}

		closeTags.push(openTag);
	}

	if (i < 0)
	{
		// Did not find a matching tag
		logger.debug('Skipping end tag with no start tag', {'tag': tag});

		return;
	}

	var keepReopening = true,
		reopenTags    = [];
	closeTags.forEach(function(openTag)
	{
		var openTagName = openTag.getName();

		// Test whether this tag should be reopened automatically
		if (keepReopening)
		{
			if (tagsConfig[openTagName].rules.flags & RULE_AUTO_REOPEN)
			{
				reopenTags.push(openTag);
			}
			else
			{
				keepReopening = false;
			}
		}

		// Output an end tag to close this start tag, then update the context
		outputTag(new Tag(Tag.END_TAG, openTagName, tag.getPos(), 0));
		popContext();
	});

	// Output our tag, moving the cursor past it, then update the context
	outputTag(tag);
	popContext();

	// Re-add tags that need to be reopened, at current cursor position
	reopenTags.forEach(function(startTag)
	{
		var newTag = addStartTag(startTag.getName(), pos, 0);

		// Copy the original tag's attributes
		newTag.setAttributes(startTag.getAttributes());

		// Re-pair the new tag
		var endTag = startTag.getEndTag();
		if (endTag)
		{
			newTag.pairWith(endTag);
		}
	});
}

/**
* Update counters and replace current context with its parent context
*/
function popContext()
{
	var tag = openTags.pop();
	--cntOpen[tag.getName()];
	context = context.parentContext;
}

/**
* Update counters and replace current context with a new context based on given tag
*
* If given tag is a self-closing tag, the context won't change
*
* @param {!Tag} tag Start tag (including self-closing)
*/
function pushContext(tag)
{
	var tagName   = tag.getName(),
		tagConfig = tagsConfig[tagName];

	++cntTotal[tagName];

	// If this is a self-closing tag, we don't need to do anything else; The context remains the
	// same
	if (tag.isSelfClosingTag())
	{
		return;
	}

	++cntOpen[tagName];
	openTags.push(tag);

	/**
	* @param {!Array} a1
	* @param {!Array} a2
	*/
	function contextAnd(a1, a2)
	{
		var i = -1, cnt = a1.length, ret = new Array(cnt);

		while (++i < cnt)
		{
			ret[i] = a1[i] & a2[i];
		}

		return ret;
	}

	// Using contextAnd() to copy the array
	var allowedChildren = contextAnd(tagConfig.allowedChildren, tagConfig.allowedChildren);

	// If the tag is transparent, we restrict its allowed children to the same set as its
	// parent, minus this tag's own disallowed children
	if (tagConfig.rules.flags & RULE_IS_TRANSPARENT)
	{
		allowedChildren = contextAnd(allowedChildren, context.allowedChildren);
	}

	// The allowedDescendants bitfield is restricted by this tag's
	var allowedDescendants = contextAnd(
		context.allowedDescendants,
		tagConfig.allowedDescendants
	);

	// Ensure that disallowed descendants are not allowed as children
	allowedChildren = contextAnd(
		allowedChildren,
		allowedDescendants
	);

	// Use this tag's flags except for noBrDescendant, which is inherited
	var flags = tagConfig.rules.flags | (context.flags & RULE_NO_BR_DESCENDANT);

	// noBrDescendant is replicated onto noBrChild
	if (flags & RULE_NO_BR_DESCENDANT)
	{
		flags |= RULE_NO_BR_CHILD;
	}

	context = {
		allowedChildren    : allowedChildren,
		allowedDescendants : allowedDescendants,
		flags              : flags,
		parentContext      : context
	};
}

/**
* Return whether given tag is allowed in current context
*
* @param  {!string}  tagName
* @return {!boolean}
*/
function tagIsAllowed(tagName)
{
	var n = tagsConfig[tagName].bitNumber;

	return !!(context.allowedChildren[n >> 5] & (1 << (n & 31)));
}