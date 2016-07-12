$(function() {

  if (typeof $.nette !== 'object') {
      return console.error('ajax-progress-bar.js: nette.ajax.js is missing');
  }


  $.nette.ext('ajax-progress-bar',
  {

    before: function ( xhr, settings )
    {
      var id = xhr . id = new Date().getTime()
      this . elements [ xhr . id ] = settings.nette.e.target;
      this . requests [ xhr . id ] = true;

      if ( this . counter === 0 )
      {
        var html = this . template ();
        $(this.parent) . append ( html );
      }

      var el = $("#" + this . id)
      var body = $(el).find(".modal-body")
      var h = $("<div id=\"" + this . idTemplate + id + "\" class=\"progress\"> \
                  <div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"100\" \
                  aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%\"><span class=\"sr-only\">Loading</span></div>\
                 </div>" )
      $(body).append ( h );   
      this . counter ++;

      el.modal({ show: false}).modal("show")

      console . log ( xhr );

    },
    error: function ( xhr, status, error )
    {
      alert ("chyba");
      this . counter --;
    /*

      console . log ( xhr );

      var id = xhr . id
      this.requests.splice(xhr.id);
      var actualElement = this.elements[xhr.id]
      this.elements.splice(xhr.id);

      var jsonResponseText = $.parseJSON(xhr.responseText);

      // remove progress bar
      $("#" + this . idTemplate + id).remove ();

      // add close button
      var el = $("#" + this . id)
      var content = $(el).find(".modal-content")
      //<a id=\"footer-" + id + "\"href=\"" + $(actualElement).attr("href")  + "\" type=\"button\" class=\"ajax btn btn-success\">Zkusit znovu</a>
      var v = this . links ( id, actualElement )
      alert(v.html())
      if ( $(el).find(".modal-footer").length() == 0 )
      {
        var footer = $("<div class=\"modal-footer\">\
          <button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" + this . closeTitle + "</button>\
          </div>")
        $(content).append ( footer );
        
      }


      // add error message
      var body = $(el).find(".modal-body")

      var element = this . flashes ( id, jsonResponseText )

      $(body).append ( element );
      $(body).append ( v );

      // bind close callback to remove the error message
      that = this;
      $("#" + this . id).on('hidden.bs.modal', function (e) {
        $(element).remove ();
        $(v).remove ();
        if ( that . isEmpty ( $(footer) ) )
          $(footer) . remove ()
      })

      $("#footer-" + id).click ( function ( e ) {
        $(element).remove ();        
        $(v).remove ();        
      });

      console.log ($(actualElement).attr("href"))
*/
    },
    success: function ( payload, status, xhr, settings )
    {
      //console . log ( payload );

      var id = xhr . id
      this.requests.splice(xhr.id);
      this.elements.splice(xhr.id);
      var actualElement = this.elements[xhr.id]

      $("#" + this . idTemplate + id) . remove ();

      var jsonResponseText = (payload);

      // add flash messages
      var el = $("#" + this . id)
      var body = $(el).find(".modal-body")
      var element = this . flashes ( id, actualElement, jsonResponseText )
      if ( element )
      {
        element . insertBefore ( $(body) . find ( ".clearfix" ) )
        var content = $(el).find(".modal-content")
        if ( this . isError ( jsonResponseText . flashes ) )
        {
          //var v = this . links ( id, actualElement )
          //$(element).find("tbody").html ($(element).find("tbody").html() + v.html());
  
          //<a id=\"footer-" + id + "\"href=\"" + $(actualElement).attr("href")  + "\" type=\"button\" class=\"ajax btn btn-success\">Zkusit znovu</a>
          //console.log ( $(el).find(".modal-footer") );
        }
        if ( $(el).find(".modal-footer") . length === 0 )
        {
          var footer = $("<div class=\"modal-footer\">\
            <button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" + this . closeTitle + "</button>\
            </div>")
          $(content).append ( footer );
          
        }
      }

      this . counter --;
      if ( this . counter === 0 )
      {
        // bind close callback to remove modal completely
        $("#" + this . id).on('hidden.bs.modal', function (e) {
          if ( element ) $(element).remove();
          $(this).remove ()
        })      
        // fire it
        if ( ! element )
          $("#" + this . id).modal ("hide");
      }
      else
      {
        $("#" + this . idTemplate + id).remove ();
        that = this
        $("#" + this . id).on('hidden.bs.modal', function (e) {
          if ( element ) $(element).remove();
          if ( footer && that . isEmpty ( $(footer) ) )
            $(footer) . remove ()
        })    
          
      }
    }
  },
  {
    // number of ajax operations
    parent: ".content",
    counter: 0,
    failed: 0,
    title: "Zpracování",
    closeTitle: "Zavřít",
    errorTitle: "Při zpracování došlo k chybě",
    id: "progress",
    idTemplate: "progress-bar-",
    errorFlag: Array ( "error" ),
    template: function () {
      return $("<div class=\"modal\" id=\"" + this . id + "\"> \
                  <div class=\"modal-dialog\"> \
                    <div class=\"modal-content\"> \
                      <div class=\"modal-header\"> \
                        <h4 class=\"modal-title\">" + this . title + "</h4> \
                      </div> \
                      <div class=\"modal-body\"> \
                        <div class=\"clearfix\"></div> \
                      </div> \
                    </div> \
                  </div> \
                </div>")
    },
    links: function ( id, actualElement ) {
      if ( actualElement && $(actualElement).attr("href") )
        return $("<a href=\"" + $(actualElement).attr("href")  + "\" type=\"button\" \
          class=\"ajax btn btn-success glyphicon glyphicon-repeat\">\
          </a>")
      else
        return $("");
      return $("<button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" + this . closeTitle + "</button>")

    },
    flashes: function ( id, target, jsonResponseText ) {
      if ( jsonResponseText . flashes === null || jsonResponseText . flashes . length <= 0 )
        return null;

      /*
        // template
        <div id="#progress-bar-1424472210591" class="">
            <div class="alert alert-error col-md-12">
              <div class="col-md-10"><span>Událost se nepodařilo smazat.</span></div>
              <div class="col-md-2">
                <a href="/concreteostrich/admin.concert/archive?do=events-1-hide" type="button" class="ajax btn btn-success glyphicon glyphicon-repeat">
                  <span class="sr-only">Znovu</span>
                </a>
              </div>
            </div>
        </div>
      */

      var element = $( "<div></div>" ) . attr ( "id", "#progress-bar-" + id )

      if ( this . isError ( jsonResponseText . flashes ) )
      {
        //$(element).append ( "<div class=\"alert alert-danger\">" + this . errorTitle + "</div>" )
      }

      var used = false
      for ( var i = 0; i < jsonResponseText . flashes . length; i ++ )
      {
        used = false
        var flash = jsonResponseText . flashes [ i ];
        var alrt = $( "<div></div>" ) . addClass ( "alert" ) . addClass ( "alert-" + flash . type ) . addClass ( "col-md-12" )
        var msg = $( "<div></div>" ) . addClass ( "col-md-10" )
        msg . append ( "<span>" + flash . message + "</span>" )

        alrt . append ( msg )

        var lnk = $( "<div></div>" ) . addClass ( "col-md-2" )
        var lnkInn = this . links ( id, target )
        console.log(target)
        if ( lnkInn != null )
        {
          $(lnkInn).click ( function ( e ) {
            $(element).hide ( "slow", function () { $(this) . remove () } );        
          });
          lnk . append ( lnkInn )
        }

        if ( ! used && this . errorFlag . indexOf(flash . type) >= 0 )
        {
          alrt . append ( lnk )
          used = true;
        }

        element . append ( alrt )
      }
      return $(element)
    },
    isEmpty: function ( el ) {
      return !$.trim(el.html())
    },
    isError: function ( flashes ) {
      if ( flashes === null || flashes . length <= 0 )
        return 0;
      for ( var i = 0; i < flashes . length; i ++ )
        if ( this . errorFlag . indexOf(flashes [ i ] . type) >= 0 )
          return 1;
      return 0;
    },

   format: function( string ) {
        var str = string.toString();
        arguments = arguments.shift ()
        if (!arguments.length)
            return str;
        var args = typeof arguments[0],
            args = (("string" == args || "number" == args) ? arguments : arguments[0]);
        for (arg in args)
            str = str.replace(RegExp("\\{" + arg + "\\}", "gi"), args[arg]);
        return str;
    },

    elements: [],
    requests: []
  }
  );

}); // function