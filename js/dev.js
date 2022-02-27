/* global CFG_GLPI */
/* global GLPI_PLUGINS_PATH */
class GlpiDevView {

   static ajax_root = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.dev + "/ajax/";
   static front_root = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.dev + "/front/";

   /**
    *
    * @param field_name
    * @return {boolean}
    */
   static isFK(field_name) {
      return (typeof field_name === 'string') && (/._id$/.test(field_name) || /._id_/.test(field_name));
   }

   static convertSchemaIdentifier(from, to, value) {
      let converted = null;
      if (from === to) {
         return value;
      }
      $.ajax({
         method: 'GET',
         url: (this.ajax_root + "schemaIdentifiersResolution.php"),
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
   }

   static objToTable(main_header, headers, obj) {
      let table = "<table class='tab_cadre_fixe'>";
      table += "<thead>";
      if (main_header !== null) {
         table += "<tr><th class='center' colspan='" + Object.keys(headers).length + "'>" + main_header + "</th></tr>";
      }
      table += "<tr>";
      $.each(headers, (hkey, htext) => {
         table += "<th>" + htext + "</th>";
      });
      table += "</tr>";
      table += "</thead><tbody>";
      let o = false;
      let embedded = $('#classview-container, #dbschemaview-container').data('embedded');
      $.each(obj, (okey, oval) => {
         if (o) {
            table += "<tr class='tab_bg_1'>";
         } else {
            table += "<tr class='tab_bg_2'>";
         }
         o = !o;
         $.each(headers, (hkey, htext) => {
            const v = (oval[hkey] || '');

            if (embedded) {
               table += "<td>" + v + "</td>";
            } else {
               if (hkey === 'table') {
                  // Is a table name
                  table += "<td>" + v + GlpiDevDBViewer.getDBSchemaButton('table', v) + GlpiDevClassViewer.getClassViewButton('table', v) + "</td>";
               } else if (hkey === 'injectable') {
                  table += "<td>" + (v === 1 ? 'Yes' : 'No') + "</td>";
               } else if (this.isFK(v)) {
                  if (v === 'items_id') {
                     const info_text = 'Refers to the ID field in any one of multiple tables. It is a polymorphic relationship.' +
                        ' Typically the itemtype it refers to is specified in the `itemtype` field.';
                     table += "<td>" + v + "<i class='info fas fa-info-circle' title='" + info_text + "'></i></td>";
                  } else {
                     table += "<td>" + v + GlpiDevDBViewer.getDBSchemaButton('fk', v) + GlpiDevClassViewer.getClassViewButton('fk', v) + "</td>";
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
   }
}

class GlpiDevClassViewer extends GlpiDevView {

   static init() {
      if ($("#classview-container").length > 0) {
         var list_items = $("#classview-container .sidebar ul li");
         if (window.location.hash !== null) {
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
            const searchParams = new URLSearchParams(window.location.search)
            searchParams.set('class_name', e.target.innerText);
            const newRelativePathQuery = window.location.pathname + '?' + searchParams.toString();
            history.pushState(null, '', newRelativePathQuery);
         });
         $("#classview-container .info-container").on('click', '.dbschema-link-btn', (e) => {GlpiDevDBViewer.onDBSchemaLinkBtnClick(e)});
         $("#classview-container .info-container").on('click', '.classview-link-btn', (e) => {GlpiDevClassViewer.onClassViewLinkBtnClick(e)});
         if ($.urlParam('class_name')) {
            this.showClassInfo($.urlParam('class_name'));
         }

         $(window).on('popstate', function() {
            this.showClassInfo($.urlParam('class_name'));
         });
      }
   }

   static onClassViewLinkBtnClick(e) {
      e.stopPropagation();
      e.preventDefault();
      const btn = $(e.target).closest('button');
      const value = this.convertSchemaIdentifier(btn.data('format'), 'class_name', btn.data('value'));
      window.location.href = this.getClassViewUrl(value);
   };

   /**
    *
    * @param class_name
    * @return {string}
    */
   static getClassViewUrl(class_name) {
      return this.front_root + "classviewer.php?class_name=" + class_name;
   }

   static getClassViewButton(format, value) {
      return `
         <button title="View Item Type" class="classview-link-btn" data-format="${format}" data-value="${value}">
            <i class="fas fa-sitemap"></i>
         </button>`;
   }

   static showClassInfo(classname) {
      const infoContainer = $("#classview-container .info-container");

      const showInfoHeader = () => {
         $("<h2>" + classname + "</h2>").appendTo(infoContainer);
         $("<div class='toolbar'>"+GlpiDevDBViewer.getDBSchemaButton('class_name', classname)+"</div>").appendTo(infoContainer);
      };
      $.ajax({
         method: 'GET',
         url: (this.ajax_root + "getClassInfo.php"),
         data: {
            class: classname
         },
         success: (data, textStatus, jqHXR) => {
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
            let extra_columns = {};
            if (GLPI_PLUGINS_PATH['datainjection'] !== undefined) {
               extra_columns['injectable'] = '[Data Injection] Injectable'
            }
            $(this.objToTable('Search Options', Object.assign({
               searchid: 'Search ID',
               table: 'Table',
               field: 'Field',
               name: 'Name',
               linkfield: 'Link field',
               datatype: 'Datatype',
               massiveaction: 'Massive action'
            }, extra_columns), data['searchoptions'])).appendTo(infoContainer);
         },
         error: function() {
            infoContainer.empty();
            showInfoHeader();
            $("<h3>No search options</h3>").appendTo(infoContainer);
         }
      });
   }
}

class GlpiDevDBViewer extends GlpiDevView {

   static init() {
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
            const searchParams = new URLSearchParams(window.location.search)
            searchParams.set('db_name', e.target.innerText);
            const newRelativePathQuery = window.location.pathname + '?' + searchParams.toString();
            history.pushState(null, '', newRelativePathQuery);
         });
         $("#dbschemaview-container .info-container").on('click', '.dbschema-link-btn', (e) => {GlpiDevDBViewer.onDBSchemaLinkBtnClick(e)});
         $("#dbschemaview-container .info-container").on('click', '.classview-link-btn', (e) => {GlpiDevClassViewer.onClassViewLinkBtnClick(e)});
         if ($.urlParam('db_name')) {
            this.showDBTableSchema($.urlParam('db_name'));
         }

         $(window).on('popstate', function() {
            this.showDBTableSchema($.urlParam('db_name'));
         });
      }
   }

   static onDBSchemaLinkBtnClick(e) {
      e.stopPropagation();
      e.preventDefault();
      const btn = $(e.target).closest('button');
      const value = this.convertSchemaIdentifier(btn.data('format'), 'table', btn.data('value'));
      window.location.href = this.getDBSchemaUrl(value);
   };

   static getDBSchemaUrl(table) {
      return this.front_root + "dbschema.php?db_name=" + table;
   }

   static getDBSchemaButton(format, value) {
      return `
         <button title="View Table" class="dbschema-link-btn" data-format="${format}" data-value="${value}">
            <i class="fas fa-table"></i>
         </button>`;
   }

   static showDBTableSchema(table) {
      const infoContainer = $("#dbschemaview-container .info-container");

      const showInfoHeader = () => {
         $("<h2>" + table + "</h2>").appendTo(infoContainer);
         $("<div class='toolbar'>"+GlpiDevClassViewer.getClassViewButton('table', table)+"</div>").appendTo(infoContainer);
      };

      $.ajax({
         method: 'GET',
         url: (this.ajax_root + "getDBTableSchema.php"),
         data: {
            table: table
         },
         success: (data, textStatus, jqHXR) => {
            infoContainer.empty();
            showInfoHeader();
            $(this.objToTable('Table Schema', {
               Field: 'Field',
               Type: 'Data Type',
               Null: 'Nullable',
               Key: 'Key Type',
               Default: 'Default Value',
               Extra: 'Extra Info'
            }, data['fields'])).appendTo(infoContainer);
            $(this.objToTable('Table Indexes', {
               Key_name: 'Key Name',
               Seq_in_index: 'Sequence in Index',
               Column_name: 'Column',
               Null: 'Nullable',
               Unique: 'Unique'
            }, data['indexes'])).appendTo(infoContainer);
         },
         error: function() {
            showInfoHeader();
            $("<h3>No schema available</h3>").appendTo(infoContainer);
         }
      });
   }
}

class GlpiDevAuditViewer {

   static init() {
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
         $("#devaudit-container .dbschema-link-btn").on('click', GlpiDevDBViewer.onDBSchemaLinkBtnClick);
         $("#devaudit-container .classview-link-btn").on('click', GlpiDevClassViewer.onClassViewLinkBtnClick);
      }
   }
}

class GlpiDevProfiler extends GlpiDevView {

   static init() {
      if ($("#devprofiler-container").length > 0) {
         const container = $("#devprofiler-container");
         const log_select = container.find('select').eq(0);
         const session_select = container.find('select').eq(1);

         log_select.on('change', (e) => {
            window.location = this.front_root + "profiler.php?log=" + e.target.value;
         });
         session_select.on('change', (e) => {
            window.location = this.front_root + "profiler.php?log=" + log_select.val() + "&session=" + e.target.value;
         });
      }
   }
}

class GlpiDevHelper {

   static init() {
      $.urlParam = (name) => {
         const results = new RegExp('[\?&]' + name + '=([^&#]*)')
            .exec(window.location.search);

         return (results !== null) ? results[1] || 0 : false;
      };

      GlpiDevClassViewer.init();
      GlpiDevDBViewer.init();
      GlpiDevAuditViewer.init();
      GlpiDevProfiler.init();
   }
}

$(document).ready(function() {
   GlpiDevHelper.init();
});
