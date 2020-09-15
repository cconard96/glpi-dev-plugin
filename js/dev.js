(function() {
   window.glpiDevHelper =  new function() {
      var self = this;
      this.ajax_root = '';
      this.front_root = '';

      var isFK = function(field_name) {
         return (typeof field_name === 'string') && (field_name.match("._id$") || field_name.match("._id_"));
      };

      var convertSchemaIdentifier = function(from, to, value) {
         var converted = null;
         if (from === to) {
            return value;
         }
         $.ajax({
            method: 'GET',
            url: (self.ajax_root + "schemaIdentifiersResolution.php"),
            data: {
               from: from,
               to: to,
               value: value
            },
            async: false,
            success: function(data) {
               converted = data;
            },
            error: function() {
               return null;
            }
         });
         return converted;
      };

      var getDBSchemaUrl = function(table) {
         return self.front_root + "dbschema.php?db_name=" + table;
      };

      var getClassViewUrl = function(class_name) {
         return self.front_root + "classviewer.php?class_name=" + class_name;
      };

      var getDBSchemaButton = function(format, value) {
         return '<button title="View Table" class="dbschema-link-btn" data-format="' + format + '" data-value="' + value + '">' +
            '<i class="fas fa-table"></i></button>';
      };

      var getClassViewButton = function(format, value) {
         return '<button title="View Item Type" class="classview-link-btn" data-format="' + format + '" data-value="' + value + '">' +
            '<i class="fas fa-sitemap"></i></button>';
      };

      var objToTable = function(main_header, headers, obj) {
         var table = "<table class='tab_cadre_fixe'>";
         table += "<thead>";
         if (main_header !== null) {
            table += "<tr><th class='center' colspan='" + Object.keys(headers).length + "'>" + main_header + "</th></tr>";
         }
         table += "<tr>";
         $.each(headers, function(hkey, htext) {
            table += "<th>" + htext + "</th>";
         });
         table += "</tr>";
         table += "</thead><tbody>";
         var o = false;
         let embedded = $('#classview-container,#dbschemaview-container').data('embedded');
         $.each(obj, function(okey, oval) {
            if (o) {
               table += "<tr class='tab_bg_1'>";
            } else {
               table += "<tr class='tab_bg_2'>";
            }
            o = !o;
            $.each(headers, function(hkey, htext) {
               var v = (oval[hkey] || '');

               if (embedded) {
                  table += "<td>" + v + "</td>";
               } else {
                  if (hkey === 'table') {
                     // Is a table name
                     table += "<td>" + v + getDBSchemaButton('table', v) + getClassViewButton('table', v) + "</td>";
                  } else if (isFK(v)) {
                     if (v === 'items_id') {
                        var info_text = 'Refers to the ID field in any one of multiple tables. It is a polymorphic relationship.' +
                           ' Typically the itemtype it refers to is specified in the `itemtype` field.';
                        table += "<td>" + v + "<i class='info fas fa-info-circle' title='" + info_text + "'></i></td>";
                     } else {
                        table += "<td>" + v + getDBSchemaButton('fk', v) + getClassViewButton('fk', v) + "</td>";
                     }
                  } else {
                     table += "<td>" + v + "</td>";
                  }
               }
            });
            table += "</tr>";
         });
         table += "</tbody></table>";
         return table;
      };

      this.showClassInfo = function(classname) {
         var infoContainer = $("#classview-container .info-container");

         var showInfoHeader = function() {
            $("<h2>" + classname + "</h2>").appendTo(infoContainer);
            $("<div class='toolbar'>"+getDBSchemaButton('class_name', classname)+"</div>").appendTo(infoContainer);
         };
         $.ajax({
            method: 'GET',
            url: (self.ajax_root + "getClassInfo.php"),
            data: {
               class: classname
            },
            success: function(data, textStatus, jqHXR) {
               infoContainer.empty();
               showInfoHeader();
               $("<div class='general-info'> \
               <p>Display Name (Singular / Plural): "+data['name'][0]+" / " + data['name'][1] + "</p>\
               <p>Icon: <i title='"+data['icon']+"' class='"+ data['icon'] + "'></i></p>\
                  </div>").appendTo(infoContainer);
               $.each(data['searchoptions'], function(i, o) {
                  o.searchid = i;
               });
               // Display warnings for missing and unlinked search options
               $.each(data['unlinked_searchoptions'], function(i, o) {
                  $(`<h4 class="warning">The search option for the field "${o}" does not have a matching DB field</h4>`).appendTo(infoContainer);
               });
               $.each(data['missing_searchoptions'], function(i, o) {
                  $(`<h4 class="warning">The field "${o}" does not have a matching search option</h4>`).appendTo(infoContainer);
               });
               $(objToTable('Search Options', {
                  searchid: 'Search ID',
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
               showInfoHeader();
               $("<h3>No search options</h3>").appendTo(infoContainer);
            }
         });
      };

      this.showDBTableSchema = function(table) {
         var infoContainer = $("#dbschemaview-container .info-container");

         var showInfoHeader = function() {
            $("<h2>" + table + "</h2>").appendTo(infoContainer);
            $("<div class='toolbar'>"+getClassViewButton('table', table)+"</div>").appendTo(infoContainer);
         };

         $.ajax({
            method: 'GET',
            url: (self.ajax_root + "getDBTableSchema.php"),
            data: {
               table: table
            },
            success: function(data, textStatus, jqHXR) {
               infoContainer.empty();
               showInfoHeader();
               $(objToTable('Table Schema', {
                  Field: 'Field',
                  Type: 'Data Type',
                  Null: 'Nullable',
                  Key: 'Key Type',
                  Default: 'Default Value',
                  Extra: 'Extra Info'
               }, data['fields'])).appendTo(infoContainer);
               $(objToTable('Table Indexes', {
                  Key_name: 'Key Name',
                  Seq_in_index: 'Sequence in Index',
                  Column_name: 'Column',
                  Null: 'Nullable',
                  Unique: 'Unique'
               }, data['indexes'])).appendTo(infoContainer);
               console.dir(data);
            },
            error: function() {
               showInfoHeader();
               $("<h3>No schema available</h3>").appendTo(infoContainer);
            }
         });
      };

      this.init = function() {
         $.urlParam = function (name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)')
               .exec(window.location.search);

            return (results !== null) ? results[1] || 0 : false;
         };

         self.ajax_root = CFG_GLPI.root_doc + "/plugins/dev/ajax/";
         self.front_root = CFG_GLPI.root_doc + "/plugins/dev/front/";

         var onClassViewLinkBtnClick = function(e) {
            e.stopPropagation();
            e.preventDefault();
            var btn = $(this);
            var value = convertSchemaIdentifier(btn.data('format'), 'class_name', btn.data('value'));
            window.location = getClassViewUrl(value);
         };
         var onDBSchemaLinkBtnClick = function(e) {
            e.stopPropagation();
            e.preventDefault();
            var btn = $(this);
            var value = convertSchemaIdentifier(btn.data('format'), 'table', btn.data('value'));
            window.location = getDBSchemaUrl(value);
         };

         if ($("#classview-container").length > 0) {
            var list_items = $("#classview-container .sidebar ul li");
            if (window.location.hash !== null) {
               console.log(list_items.find('a[href="'+window.location.hash+'"]'));
               $.urlParam('class_name');
               list_items.find('a[href="'+window.location.hash+'"]').click();
            }
            $("#classview-container .sidebar input[name='search']").on('input', function(e) {
               var filter = $("#classview-container .sidebar input[name='search']").val().toLowerCase();
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
               self.showClassInfo(e.target.innerText);
            });
            $("#classview-container .info-container").on('click', '.dbschema-link-btn', onDBSchemaLinkBtnClick);
            $("#classview-container .info-container").on('click', '.classview-link-btn', onClassViewLinkBtnClick);
            if ($.urlParam('class_name')) {
               self.showClassInfo($.urlParam('class_name'));
            }
         }

         if ($("#dbschemaview-container").length > 0) {
            var list_items = $("#dbschemaview-container .sidebar ul li");
            $("#dbschemaview-container .sidebar input[name='search']").on('input', function(e) {
               var filter = $("#dbschemaview-container .sidebar input[name='search']").val().toLowerCase();
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
               self.showDBTableSchema(e.target.innerText);
            });
            $("#dbschemaview-container .info-container").on('click', '.dbschema-link-btn', onDBSchemaLinkBtnClick);
            $("#dbschemaview-container .info-container").on('click', '.classview-link-btn', onClassViewLinkBtnClick);
            if ($.urlParam('db_name')) {
               self.showDBTableSchema($.urlParam('db_name'));
            }
         }

         if ($("#devaudit-container").length > 0) {
            const hide_ok_check = $('#devaudit-container input[name="hide_ok_classes"]');
            if (hide_ok_check.is(':checked')) {
               $("#devaudit-container details").filter(function () {
                  return $(this).attr('data-issue-count') == 0;
               }).hide();
            }
            hide_ok_check.on('click', function(e) {
               if (hide_ok_check.is(':checked')) {
                  $("#devaudit-container details").filter(function () {
                     return $(this).attr('data-issue-count') == 0;
                  }).hide();
               } else {
                  $("#devaudit-container details").filter(function () {
                     return $(this).attr('data-issue-count') == 0;
                  }).show();
               }
            });
            $("#devaudit-container .dbschema-link-btn").on('click', onDBSchemaLinkBtnClick);
            $("#devaudit-container .classview-link-btn").on('click', onClassViewLinkBtnClick);
         }
      };
   };
})();

$(document).ready(function() {
   window.glpiDevHelper.init();
});