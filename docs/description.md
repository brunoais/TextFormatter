* Text formatting library. Goal is to be used as a dependency in forum softwares, blogs and what not
* Currently being worked on, about alpha status
* 1661 tests covering 4869 of 4906 lines of code. Test suite runs on every commit
* Uses plugins: Autolink, BBCodes, Censor, Emoticons, HTMLElements, plus a few more
* Written on PHP 5.4, works on PHP 5.3
* PHP extensions required: dom, filter, pcre with Unicode support, xsl. All four are enabled by default but contingency plan possible for ext/xsl if it turns out that a number of hosts disable it
