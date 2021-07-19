(() => {
   self.ajax_root = CFG_GLPI.root_doc + "/plugins/dev/ajax/";
   self.front_root = CFG_GLPI.root_doc + "/plugins/dev/front/";

   const injectControls = (plugins) => {
      const dir_col_num = $('.search-results th[data-searchopt-id="2"]').index();
      const action_col_num = $('.search-results th[data-searchopt-id="8"]').index();
      const rows = $('.search-results tbody tr');

      $.each(rows, (i, row) => {
         const dir = $(row).find('td').eq(dir_col_num).text();
         if (plugins.includes(dir)) {
            $(`
               <a class="pointer" href="${this.front_root + '/plugincreator.form.php?id='+dir}">
                  <span class="fas fa-pencil-alt fa-fw fa-2x" title="Edit">
                     <span class="sr-only">Edit</span>
                  </span>
               </a>
            `).prependTo($(row).find('td').eq(action_col_num).find('div'));
         }
      });
   }
   $.ajax({
      method: 'GET',
      url: (self.ajax_root + "pluginCreator.php"),
      data: {
         action: 'get_plugins'
      }
   }).done((plugins) => {
      if (typeof plugins === "undefined") {
         plugins = [];
      }
      if (typeof plugins === "object") {
         plugins = Object.values(plugins);
      }
      injectControls(plugins);
      $(document).on('search_refresh', '#massformPlugin', () => {
         injectControls(plugins);
      });
   });
})();
