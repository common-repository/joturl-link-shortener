/*
 Select2 4.0.6-rc.1 | https://github.com/select2/select2/blob/master/LICENSE.md */
(function(){if(jQuery&&jQuery.fn&&jQuery.fn.select2&&jQuery.fn.select2.amd)var e=jQuery.fn.select2.amd;return e.define("select2/i18n/uk",[],function(){function e(e,t,n,r){return e%100>10&&e%100<15?r:e%10===1?t:e%10>1&&e%10<5?n:r}return{errorLoading:function(){return"\u041d\u0435\u043c\u043e\u0436\u043b\u0438\u0432\u043e \u0437\u0430\u0432\u0430\u043d\u0442\u0430\u0436\u0438\u0442\u0438 \u0440\u0435\u0437\u0443\u043b\u044c\u0442\u0430\u0442\u0438"},inputTooLong:function(t){var n=t.input.length-t.maximum;
return"\u0411\u0443\u0434\u044c \u043b\u0430\u0441\u043a\u0430, \u0432\u0438\u0434\u0430\u043b\u0456\u0442\u044c "+n+" "+e(t.maximum,"\u043b\u0456\u0442\u0435\u0440\u0443","\u043b\u0456\u0442\u0435\u0440\u0438","\u043b\u0456\u0442\u0435\u0440")},inputTooShort:function(e){var t=e.minimum-e.input.length;return"\u0411\u0443\u0434\u044c \u043b\u0430\u0441\u043a\u0430, \u0432\u0432\u0435\u0434\u0456\u0442\u044c "+t+" \u0430\u0431\u043e \u0431\u0456\u043b\u044c\u0448\u0435 \u043b\u0456\u0442\u0435\u0440"},
loadingMore:function(){return"\u0417\u0430\u0432\u0430\u043d\u0442\u0430\u0436\u0435\u043d\u043d\u044f \u0456\u043d\u0448\u0438\u0445 \u0440\u0435\u0437\u0443\u043b\u044c\u0442\u0430\u0442\u0456\u0432\u2026"},maximumSelected:function(t){return"\u0412\u0438 \u043c\u043e\u0436\u0435\u0442\u0435 \u0432\u0438\u0431\u0440\u0430\u0442\u0438 \u043b\u0438\u0448\u0435 "+t.maximum+" "+e(t.maximum,"\u043f\u0443\u043d\u043a\u0442","\u043f\u0443\u043d\u043a\u0442\u0438","\u043f\u0443\u043d\u043a\u0442\u0456\u0432")},
noResults:function(){return"\u041d\u0456\u0447\u043e\u0433\u043e \u043d\u0435 \u0437\u043d\u0430\u0439\u0434\u0435\u043d\u043e"},searching:function(){return"\u041f\u043e\u0448\u0443\u043a\u2026"}}}),{define:e.define,require:e.require}})();
