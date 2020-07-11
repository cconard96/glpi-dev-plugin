(function() {
   window.glpiDevHelper = function() {
      var self = this;
      this.ajax_root = '';

      var objToTable = function(headers, obj) {
         var table = "<table class='tab_cadre_fixe'>";
         table += "<thead><th>Search ID</th>";
         $.each(headers, function(hkey, htext) {
            table += "<th>" + htext + "</th>";
         });
         table += "</thead><tbody>";
         var o = false;
         $.each(obj, function(okey, oval) {
            if (isNaN(okey)) {
               return;
            }
            if (o) {
               table += "<tr class='tab_bg_1'>";
            } else {
               table += "<tr class='tab_bg_2'>";
            }
            o = !o;
            table += "<td>" + okey + "</td>";
            $.each(headers, function(hkey, htext) {
               table += "<td>" + (oval[hkey] || '') + "</td>";
            });
            table += "</tr>";
         });
         table += "</tbody></table>";
         return table;
      };

      this.showInfo = function(classname) {
         var infoContainer = $("#classview-container .classview-info");
         $.ajax({
            method: 'GET',
            url: (self.ajax_root + "getClassInfo.php"),
            data: {
               class: classname
            },
            success: function(data, textStatus, jqHXR) {
               infoContainer.empty();
               $("<button class='btn-tab-searchoptions'>Search options</button>").appendTo(infoContainer);
               $(objToTable({
                  table: 'Table',
                  field: 'Field',
                  name: 'Name',
                  linkfield: 'Link field',
                  datatype: 'Datatype',
                  massiveaction: 'Massive action'
               }, data['searchoptions'])).appendTo(infoContainer);
            },
            error: function() {
               infoContainer.empty();
               $("<button class='btn-tab-searchoptions'>Search options</button>").appendTo(infoContainer);
               $("<h3>No search options</h3>").appendTo(infoContainer);
            }
         });
      };

      this.init = function() {
         self.ajax_root = CFG_GLPI.root_doc + "/plugins/dev/ajax/";

         if ($("#classview-container").length > 0) {
            var list_items = $("#classview-container .classview-sidebar ul li");
            $("#classview-container .classview-sidebar input[name='search']").on('input', function(e) {
               var filter = $("#classview-container .classview-sidebar input[name='search']").val().toLowerCase();
               $.each(list_items, function(ind, item) {
                  item = $(item);
                  if (!item.text().toLowerCase().includes(filter)) {
                     item.hide();
                  } else {
                     item.show();
                  }
               });
            });
            list_items.on('click', 'a', function(e) {
               self.showInfo(e.target.innerText);
            });
         }
      };
   };
})();

$(document).ready(function() {
   var devHelper = new window.glpiDevHelper();
   devHelper.init();
});