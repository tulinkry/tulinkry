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

      //console . log ( xhr );
    },
    error: function ( xhr, status, error )
    {
      this . counter --;

      //console . log ( xhr );

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
      //alert(v.html())
      if ( this.isEmpty ( $(el).find(".modal-footer") ) )
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

      //console.log ($(actualElement).attr("href"))

    },
    success: function ( payload, status, xhr, settings )
    {
      //console . log ( payload );

      var id = xhr . id
      this.requests.splice(xhr.id);
      this.elements.splice(xhr.id);
      var actualElement = this.elements[xhr.id]

      $("#" + this . idTemplate + id).remove ();

      var jsonResponseText = (payload);

      // add flash messages
      var el = $("#" + this . id)
      var body = $(el).find(".modal-body")
      var element = this . flashes ( id, jsonResponseText )
      if ( element )
      {
        $(body).append ( element );
        if ( this . isError ( jsonResponseText . flashes ) )
        {
          var v = this . links ( id, actualElement )
          $(body).append (v);
  
          $("#footer-" + id).click ( function ( e ) {
            $(element).remove ();        
            $(v).remove ();        
          });


          var content = $(el).find(".modal-content")
          //<a id=\"footer-" + id + "\"href=\"" + $(actualElement).attr("href")  + "\" type=\"button\" class=\"ajax btn btn-success\">Zkusit znovu</a>
         // console.log ( $(el).find(".modal-footer") );
          if ( $(el).find(".modal-footer") . length === 0 )
          {
            var footer = $("<div class=\"modal-footer\">\
              <button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" + this . closeTitle + "</button>\
              </div>")
            $(content).append ( footer );
            
          }
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
    parent: "body",
    counter: 0,
    failed: 0,
    title: "Zpracování",
    closeTitle: "Zavřít",
    errorTitle: "Při zpracování došlo k chybě",
    id: "progress",
    idTemplate: "progress-bar-",
    template: function () {
      return $("<div class=\"modal\" id=\"" + this . id + "\"> \
                  <div class=\"modal-dialog\"> \
                    <div class=\"modal-content\"> \
                      <div class=\"modal-header\"> \
                        <h4 class=\"modal-title\">" + this . title + "</h4> \
                      </div> \
                      <div class=\"modal-body\"> \
                      </div> \
                    </div> \
                  </div> \
                </div>")
    },
    links: function ( id, actualElement ) {
      if ( actualElement && $(actualElement).attr("href") )
        return $("<a id=\"footer-" + id + "\"href=\"" + $(actualElement).attr("href")  + "\" type=\"button\" class=\"ajax btn btn-success\">Zkusit znovu</a>")
      else
        return $("");
      return $("<button type=\"button\" class=\"btn btn-danger\" data-dismiss=\"modal\">" + this . closeTitle + "</button>")

    },
    flashes: function ( id, jsonResponseText ) {
      if ( ! jsonResponseText . flashes || jsonResponseText . flashes === null || jsonResponseText . flashes . length <= 0 )
        return null;

      var element = $("<div id=\"#" + this . idTemplate + id + "\"></div>" )
      if ( this . isError ( jsonResponseText . flashes ) )
      {
        $(element).append ( "<div class=\"alert alert-danger\">" + this . errorTitle + "</div>" )
      }
      //$(element).append ( "<div class=\"alert alert-danger\">" + xhr . status + " " + error + "</div>" )

      for ( var i = 0; i < jsonResponseText . flashes . length; i ++ )
      {
        $(element).append ( "<div class=\"alert alert-" + jsonResponseText . flashes [ i ] . type + "\">" + jsonResponseText . flashes [ i ] . message + "</div>" )
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
        if ( flashes [ i ] . type === "error" )
          return 1;
      return 0;
    },
    elements: [],
    requests: []
  }
  );

}); // function