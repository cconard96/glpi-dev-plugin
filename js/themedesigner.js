function getKnownThemeVariables() {
   var props = {
      'Login': {
         'login-background-color': 'Page Background Color',
         'login-header-background-color': 'Header Background Color',
         'login-text-color': 'Text Color'
      },
      'Common': {
         'default-icon-color': 'Default Icon Color',
         'default-text-color': 'Default Text Color',
         'page-background': 'Page Background Color',
         'content-background': 'Content Background Color',
         'footer-background-color': 'Footer Background Color',
         'footer-color': 'Footer Color'
      },
      'Navigation': {
         'header-top-background-color': 'Top Header Background Color',
         'header-bottom-background-color': 'Bottom Header Background Color',
         'header-top-color': 'Top Header Color',
         'header-bottom-color': 'Bottom Header Color',
         'nav-current-color': 'Current Item Color',
         'nav-current-background-color': 'Current Item Background Color',
         'nav-hover-color': 'Hover Color',
         'nav-hover-background-color': 'Hover Background Color'
      },
      'Breadcrumbs': {
         'breadcrumb-bar-background-color': 'Bar Background Color',
         'breadcrumb-bar-color': 'Bar Color',
         'breadcrumb-background-color': 'Item Background Color',
         'breadcrumb-active-background-color': 'Active Item Background Color',
         'breadcrumb-active-color': 'Active Item Color'
      },
      'Buttons': {
         'submit-btn-background-color': 'Submit Button Background Color',
         'submit-btn-color': 'Submit Button Color',
         'secondary-btn-background-color': 'Secondary Button Background Color',
         'secondary-btn-color': 'Secondary Button Color'
      },
      'Forms': {
         'formtab-background-color': 'Tab Background Color',
         'formtab-color': 'Tab Color',
         'formheader-background-color': 'Header Background Color',
         'formfield-background-color': 'Field Background Color',
         'formfield-color': 'Field Color'
      },
      'Links': {
         'link-color': 'Color',
         'link-hover-color': 'Hover Color'
      },
      'Tables': {
         'table-header-background-color': 'Header Background Color',
         'table-header-hover-background-color': 'Header Hover Background Color',
         'table-header-color': 'Header Color',
         'table-row-odd-background-color': 'Odd Row Background Color',
         'table-row-odd-color': 'Odd Row Color',
         'table-row-even-background-color': 'Even Row Background Color',
         'table-row-even-color': 'Even Row Color',
         'table-row-error-background-color': 'Error Row Background Color',
         'table-row-error-color': 'Error Row Color',
      },
   };
   return props;
}

function loadThemeDesigner() {
   var designer = $('#themedesigner-designer');
   if (typeof designer === 'undefined') {
      return;
   }

   var props = getKnownThemeVariables();

   var content = "<table width='400px' style='margin: auto'>";
   $.each(props, function(group, fields) {
      content += "<tr><th colspan='2' style='font-size: 1.5em'>"+group+"</th></tr>";
      $.each(fields, function(name, display_name) {
         var val = getComputedStyle(document.documentElement).getPropertyValue('--' + name).trim();
         content += "<tr><td style='font-size: 1.15em; text-align: left'>"+display_name+"</td>";
         content += "<td><input name='" + name + "' type='color' value='"+val+"' style='height: 32px' onchange='updateCssVar(this)'/>";
         content += "<td><input name='_" + name + "' type='text' value='"+val+"' style='height: 32px' onkeyup='updateCssVar(this)'/></td></tr>";
      });
   });
   content += "</table>";

   content = "<div style='margin-left: 10%'>";
   $.each(props, function(group, fields) {
      content += "<details><summary style='font-size: 1.5em; text-align: left; cursor: pointer'>"+group+"</summary><table>";
      $.each(fields, function(name, display_name) {
         var val = getComputedStyle(document.documentElement).getPropertyValue('--' + name).trim();
         content += "<tr><td style='font-size: 1.2em; text-align: left'>"+display_name+"</td>";
         content += "<td><input name='" + name + "' type='color' value='"+val+"' style='height: 32px' onchange='updateCssVar(this)'/>";
         content += "<td><input name='_" + name + "' type='text' value='"+val+"' style='height: 32px' onkeyup='updateCssVar(this)'/></td></tr>";
      });
      content += "</table></details>";
   });
   content += "</div>";

   content += "<div class='center'>";
   content += "<input style='background-color: var(--secondary-btn-background-color); color: var(--secondary-btn-color)' type='button' onclick='generateRootVariables()' value='Generate'/>";
   content += "</div>";
   $(content).appendTo(designer);
}

function isHexColor (hex) {
   if (hex.startsWith('#')) {
      hex = hex.substr(1);
   }
   return typeof hex === 'string'
      && hex.length === 6
      && !isNaN(Number('0x' + hex));
}

function updateCssVar(input_el) {
   var name = input_el.getAttribute('name');
   if (name.startsWith('_')) {
      name = name.substr(1);
   }
   if (!isHexColor(input_el.value)) {
      return;
   }
   $(':root').css('--'+name, input_el.value);
   if (input_el.getAttribute("type") === 'color') {
      $("input[name='_"+name+"']").attr('value', input_el.value);
   } else {
      $("input[name='"+name+"']").attr('value', input_el.value);
   }
}

function generateRootVariables() {
   var props = getKnownThemeVariables();
   var result = ":root {</br>";
   $.each(props, function(group, fields) {
      $.each(fields, function(name, display_name) {
         result += "   --" + name + ": " + $(':root').css('--'+name) + ";</br>";
      });
      result += "</br>";
   });
   result += "}";
   var result_element = $('#themedesigner-results');
   result_element.empty();
   //$(result).appendTo(result_element);
   result_element.html(result);
}