/*
 Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */
(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/ps",[],function(){return{errorLoading:function(){return"\u067e\u0627\u064a\u0644\u064a \u0646\u0647 \u0633\u064a \u062a\u0631\u0644\u0627\u0633\u0647 \u06a9\u06d0\u062f\u0627\u06cc"},inputTooLong:function(e){var t=e.input.length-e.maximum,n="\u062f \u0645\u0647\u0631\u0628\u0627\u0646\u06cd \u0644\u0645\u062e\u064a "+t+" \u062a\u0648\u0631\u06cc \u0693\u0646\u06ab \u06a9\u0693\u0626";
return t!=1&&(n=n.replace("\u062a\u0648\u0631\u06cc","\u062a\u0648\u0631\u064a")),n},inputTooShort:function(e){var t=e.minimum-e.input.length,n="\u0644\u0696 \u062a\u0631 \u0644\u0696\u0647 "+t+" \u064a\u0627 \u0689\u06d0\u0631 \u062a\u0648\u0631\u064a \u0648\u0644\u064a\u06a9\u0626";return n},loadingMore:function(){return"\u0646\u0648\u0631\u064a \u067e\u0627\u064a\u0644\u064a \u062a\u0631\u0644\u0627\u0633\u0647 \u06a9\u064a\u0696\u064a..."},maximumSelected:function(e){var t="\u062a\u0627\u0633\u0648 \u064a\u0648\u0627\u0632\u064a "+
e.maximum+" \u0642\u0644\u0645 \u067e\u0647 \u0646\u069a\u0647 \u06a9\u0648\u0644\u0627\u06cc \u0633\u06cc";return e.maximum!=1&&(t=t.replace("\u0642\u0644\u0645","\u0642\u0644\u0645\u0648\u0646\u0647")),t},noResults:function(){return"\u067e\u0627\u064a\u0644\u064a \u0648 \u0646\u0647 \u0645\u0648\u0646\u062f\u0644 \u0633\u0648\u06d0"},searching:function(){return"\u0644\u067c\u0648\u0644 \u06a9\u064a\u0696\u064a..."}}}),{define:e.define,require:e.require}})();
