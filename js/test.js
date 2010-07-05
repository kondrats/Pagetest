function ValidateInput(form)
{
    if( form.url.value == "" )
    {
        alert( "Please enter an URL to test." );
        form.url.focus();
        return false
    }
    
    var runs = form.runs.value;
    if( runs < 1 || runs > maxRuns )
    {
        alert( "Please select a number of runs between 1 and " + maxRuns + "." );
        form.runs.focus();
        return false
    }
    
    var date = new Date();
    date.setTime(date.getTime()+(730*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
    var options = 0;
    if( form.private.checked )
        options = 1;
    if( form.viewFirst.checked )
        options = options | 2;
    document.cookie = 'testOptions=' + options + expires + '; path=/';
    document.cookie = 'runs=' + runs + expires + '; path=/';
    
    // save out the selected location and connection information
    document.cookie = 'cfg=' + $('#connection').val() + expires +  '; path=/';
    document.cookie = 'u=' + $('#bwUp').val() + expires +  '; path=/';
    document.cookie = 'd=' + $('#bwDown').val() + expires +  '; path=/';
    document.cookie = 'l=' + $('#latency').val() + expires +  '; path=/';
    document.cookie = 'p=' + $('#plr').val() + expires +  '; path=/';

    return true;
}

/*
    Do any populating of the input form based on the loaded location information
*/
$(document).ready(function(){ 

    // enable tooltips
    $("#DOMElement").tooltip({ position: "top center", offset: [-5, 0]  });  
        
   // handle when the selection changes for the location
    $("input[name=where]").click(function(){
        LocationChanged();
    });

    $("#browser").change(function(){
        BrowserChanged();
    });

    $("#connection").change(function(){
        ConnectionChanged();
    });
    
    // make sure to select an intelligent default (in case the back button was hit)
    LocationChanged();
    
    $('#url').focus();
});

/*
    Populate the different browser options for a given location
*/
function LocationChanged()
{
    var loc = $('input[name=where]:checked').val() 
    
    var browsers = [];
    var defaultConfig = locations[loc]['default'];
    if( defaultConfig == undefined )
        defaultConfig = locations[loc]['1'];
    var defaultBrowser = locations[defaultConfig]['browser'];
    
    // build the list of browsers for this location
    for( var key in locations[loc] )
    {
        // only care about the integer indexes
        if( !isNaN(key) )
        {
            var config = locations[loc][key];
            var browser = locations[config]['browser'];
            if( browser != undefined )
            {
                // see if we already know about this browser
                var browserKey = browser.replace(" ","");
                browsers[browserKey] = browser;
            }
        }
    }
    
    // fill in the browser list, selecting the default one
    browserHtml = '';
    for( var key in browsers )
    {
        var browser = browsers[key];
        var selected = '';
        if( browser == defaultBrowser )
            selected = ' selected';
        browserHtml += '<option value="' + key + '"' + selected + '>' + browser + '</option>';
    }
    $('#browser').html(browserHtml);
    
    BrowserChanged();
}

/*
    Populate the various connection types that are available
*/
function BrowserChanged()
{
    var loc = $('input[name=where]:checked').val() 
    var selectedBrowser = $('#browser').val();
    var defaultConfig = locations[loc]['default'];
    var selectedConfig;
    
    var connections = [];

    // build the list of connections for this location/browser
    for( var key in locations[loc] )
    {
        // only care about the integer indexes
        if( !isNaN(key) )
        {
            var config = locations[loc][key];
            var browser = locations[config]['browser'].replace(" ","");;
            if( browser == selectedBrowser )
            {
                if( locations[config]['connectivity'] != undefined )
                {
                    connections[config] = locations[config]['connectivity'];
                    if( config == defaultConfig )
                        selectedConfig = config;
                }
                else
                {
                    for( var conn in connectivity )
                    {
                        if( selectedConfig == undefined )
                            selectedConfig = config + '.' + conn;
                        connections[config + '.' + conn] = connectivity[conn]['label'];
                    }
                    
                    connections[config + '.custom'] = 'Custom';
                    if( selectedConfig == undefined )
                        selectedConfig = config + '.custom';
                }
            }
        }
    }
    
    // if the default configuration couldn't be selected, pick the first one
    if( selectedConfig == undefined )
    {
        for( var config in connections )
        {
            selectedConfig = config;
            break;
        }
    }
    
    // build the actual list
    connectionHtml = '';
    for( var config in connections )
    {
        var selected = '';
        if( config == selectedConfig )
            selected = ' selected';
        connectionHtml += '<option value="' + config + '"' + selected + '>' + connections[config] + '</option>';
    }
    $('#connection').html(connectionHtml);
    
    ConnectionChanged();
}

/*
    Populate the specifics of the connection information
*/
function ConnectionChanged()
{
    var conn = $('#connection').val();
    if( conn != undefined && conn.length )
    {
        var parts = conn.split('.');
        var config = parts[0];
        var connection = parts[1];
        var setSpeed = true;
        
        var backlog = locations[config]['backlog'];

        var up = locations[config]['up'] / 1000;
        var down = locations[config]['down'] / 1000;
        var latency = locations[config]['latency'];
        var plr = 0;
        if( connection != undefined && connection.length )
        {
            if( connectivity[connection] != undefined )
            {
                up = connectivity[connection]['bwOut'] / 1000;
                down = connectivity[connection]['bwIn'] / 1000;
                latency = connectivity[connection]['latency'];
                if( connectivity[connection]['plr'] != undefined )
                    plr = connectivity[connection]['plr'];
            }
            else
            {
                setSpeed = false;
            }
        }

        if( setSpeed )
        {
            $('#bwDown').val(down);
            $('#bwUp').val(up);
            $('#latency').val(latency);
            $('#plr').val(plr);
        }
        
        // enable/disable the fields as necessary
        if( connection == 'custom' )
        {
            $('#bwDown').removeAttr("disabled");
            $('#bwUp').removeAttr("disabled");
            $('#latency').removeAttr("disabled");
            $('#plr').removeAttr("disabled");
        }
        else
        {
            $('#bwDown').attr("disabled", "disabled");
            $('#bwUp').attr("disabled", "disabled");
            $('#latency').attr("disabled", "disabled");
            $('#plr').attr("disabled", "disabled");
        }
        
        $('#backlog').html(backlog);
        if( backlog < 5 )
            $('#backlog').removeClass('backlogWarn , backlogHigh');
        else if( backlog < 20 )
            $('#backlog').removeClass('backlogHigh').addClass("backlogWarn");
        else
            $('#backlog').removeClass('backlogWarn').addClass("backlogHigh");
    }
}

/*
 * jquery.tools 1.1.1 - The missing UI library for the Web
 * 
 * [tools.tabs-1.0.3, tools.tooltip-1.1.1]
 * 
 * Copyright (c) 2009 Tero Piirainen
 * http://flowplayer.org/tools/
 *
 * Dual licensed under MIT and GPL 2+ licenses
 * http://www.opensource.org/licenses
 * 
 * -----
 * 
 * File generated: Fri Sep 18 14:30:41 GMT+00:00 2009
 */
(function(d){d.tools=d.tools||{};d.tools.tabs={version:"1.0.3",conf:{tabs:"a",current:"current",onBeforeClick:null,onClick:null,effect:"default",initialIndex:0,event:"click",api:false,rotate:false},addEffect:function(e,f){c[e]=f}};var c={"default":function(f,e){this.getPanes().hide().eq(f).show();e.call()},fade:function(g,e){var f=this.getConf(),h=f.fadeOutSpeed,j=this.getCurrentPane();if(h){j.fadeOut(h)}else{j.hide()}this.getPanes().eq(g).fadeIn(f.fadeInSpeed,e)},slide:function(f,e){this.getCurrentPane().slideUp(200);this.getPanes().eq(f).slideDown(400,e)},ajax:function(f,e){this.getPanes().eq(0).load(this.getTabs().eq(f).attr("href"),e)}};var b;d.tools.tabs.addEffect("horizontal",function(f,e){if(!b){b=this.getPanes().eq(0).width()}this.getCurrentPane().animate({width:0},function(){d(this).hide()});this.getPanes().eq(f).animate({width:b},function(){d(this).show();e.call()})});function a(g,h,f){var e=this,j=d(this),i;d.each(f,function(k,l){if(d.isFunction(l)){j.bind(k,l)}});d.extend(this,{click:function(k){var o=e.getCurrentPane();var l=g.eq(k);if(typeof k=="string"&&k.replace("#","")){l=g.filter("[href*="+k.replace("#","")+"]");k=Math.max(g.index(l),0)}if(f.rotate){var m=g.length-1;if(k<0){return e.click(m)}if(k>m){return e.click(0)}}if(!l.length){if(i>=0){return e}k=f.initialIndex;l=g.eq(k)}var n=d.Event("onBeforeClick");j.trigger(n,[k]);if(n.isDefaultPrevented()){return}if(k===i){return e}l.addClass(f.current);c[f.effect].call(e,k,function(){j.trigger("onClick",[k])});g.removeClass(f.current);l.addClass(f.current);i=k;return e},getConf:function(){return f},getTabs:function(){return g},getPanes:function(){return h},getCurrentPane:function(){return h.eq(i)},getCurrentTab:function(){return g.eq(i)},getIndex:function(){return i},next:function(){return e.click(i+1)},prev:function(){return e.click(i-1)},bind:function(k,l){j.bind(k,l);return e},onBeforeClick:function(k){return this.bind("onBeforeClick",k)},onClick:function(k){return this.bind("onClick",k)},unbind:function(k){j.unbind(k);return e}});g.each(function(k){d(this).bind(f.event,function(l){e.click(k);return l.preventDefault()})});if(location.hash){e.click(location.hash)}else{e.click(f.initialIndex)}h.find("a[href^=#]").click(function(){e.click(d(this).attr("href"))})}d.fn.tabs=function(i,f){var g=this.eq(typeof f=="number"?f:0).data("tabs");if(g){return g}if(d.isFunction(f)){f={onBeforeClick:f}}var h=d.extend({},d.tools.tabs.conf),e=this.length;f=d.extend(h,f);this.each(function(l){var j=d(this);var k=j.find(f.tabs);if(!k.length){k=j.children()}var m=i.jquery?i:j.children(i);if(!m.length){m=e==1?d(i):j.parent().find(i)}g=new a(k,m,f);j.data("tabs",g)});return f.api?g:this}})(jQuery);
(function(c){c.tools=c.tools||{};c.tools.tooltip={version:"1.1.1",conf:{effect:"toggle",fadeOutSpeed:"fast",tip:null,predelay:0,delay:30,opacity:1,lazy:undefined,position:["top","center"],offset:[0,0],cancelDefault:true,relative:false,events:{def:"mouseover,mouseout",input:"focus,blur",widget:"focus mouseover,blur mouseout"},api:false},addEffect:function(d,f,e){b[d]=[f,e]}};var b={toggle:[function(d){var e=this.getConf();this.getTip().css({opacity:e.opacity}).show();d.call()},function(d){this.getTip().hide();d.call()}],fade:[function(d){this.getTip().fadeIn(this.getConf().fadeInSpeed,d)},function(d){this.getTip().fadeOut(this.getConf().fadeOutSpeed,d)}]};function a(e,f){var o=this,j=c(this);e.data("tooltip",o);var k=e.next();if(f.tip){k=c(f.tip);if(k.length>1){k=e.nextAll(f.tip).eq(0);if(!k.length){k=e.parent().nextAll(f.tip).eq(0)}}}function n(t){var s=f.relative?e.position().top:e.offset().top,r=f.relative?e.position().left:e.offset().left,u=f.position[0];s-=k.outerHeight()-f.offset[0];r+=e.outerWidth()+f.offset[1];var p=k.outerHeight()+e.outerHeight();if(u=="center"){s+=p/2}if(u=="bottom"){s+=p}u=f.position[1];var q=k.outerWidth()+e.outerWidth();if(u=="center"){r-=q/2}if(u=="left"){r-=q}return{top:s,left:r}}var h=e.is(":input"),d=h&&e.is(":checkbox, :radio, select, :button"),g=e.attr("type"),m=f.events[g]||f.events[h?(d?"widget":"input"):"def"];m=m.split(/,\s*/);e.bind(m[0],function(q){var p=k.data("trigger");if(p&&p[0]!=this){k.hide()}q.target=this;o.show(q);k.hover(o.show,function(){o.hide(q)})});e.bind(m[1],function(p){o.hide(p)});if(!c.browser.msie&&!h){e.mousemove(function(){if(!o.isShown()){e.triggerHandler("mouseover")}})}if(f.opacity<1){k.css("opacity",f.opacity)}var l=0,i=e.attr("title");if(i&&f.cancelDefault){e.removeAttr("title");e.data("title",i)}c.extend(o,{show:function(q){if(q){e=c(q.target)}clearTimeout(k.data("timer"));if(k.is(":animated")||k.is(":visible")){return o}function p(){k.data("trigger",e);var s=n(q);if(f.tip&&i){k.html(e.data("title"))}var r=c.Event("onBeforeShow");j.trigger(r,[s]);if(r.isDefaultPrevented()){return o}s=n(q);k.css({position:"absolute",top:s.top,left:s.left});b[f.effect][0].call(o,function(){j.trigger("onShow")})}if(f.predelay){clearTimeout(l);l=setTimeout(p,f.predelay)}else{p()}return o},hide:function(q){clearTimeout(k.data("timer"));clearTimeout(l);if(!k.is(":visible")){return}function p(){var r=c.Event("onBeforeHide");j.trigger(r);if(r.isDefaultPrevented()){return}b[f.effect][1].call(o,function(){j.trigger("onHide")})}if(f.delay&&q){k.data("timer",setTimeout(p,f.delay))}else{p()}return o},isShown:function(){return k.is(":visible, :animated")},getConf:function(){return f},getTip:function(){return k},getTrigger:function(){return e},bind:function(p,q){j.bind(p,q);return o},onHide:function(p){return this.bind("onHide",p)},onBeforeShow:function(p){return this.bind("onBeforeShow",p)},onShow:function(p){return this.bind("onShow",p)},onBeforeHide:function(p){return this.bind("onBeforeHide",p)},unbind:function(p){j.unbind(p);return o}});c.each(f,function(p,q){if(c.isFunction(q)){o.bind(p,q)}})}c.prototype.tooltip=function(d){var e=this.eq(typeof d=="number"?d:0).data("tooltip");if(e){return e}var f=c.extend(true,{},c.tools.tooltip.conf);if(c.isFunction(d)){d={onBeforeShow:d}}else{if(typeof d=="string"){d={tip:d}}}d=c.extend(true,f,d);if(typeof d.position=="string"){d.position=d.position.split(/,?\s/)}if(d.lazy!==false&&(d.lazy===true||this.length>20)){this.one("mouseover",function(g){e=new a(c(this),d);e.show(g)})}else{this.each(function(){e=new a(c(this),d)})}return d.api?e:this}})(jQuery);
