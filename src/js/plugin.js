(function ($) {
  
  acf.fields.external_relationship = acf.fields.relationship.extend({
    type: 'external_relationship',

    focus : function () {
      this.$el = this.$field.find('.acf-external_relationship');
      this.$input = this.$el.find('.acf-hidden input');
      this.$choices = this.$el.find('.choices'), 
        this.$values = this.$el.find('.values');

      // Get options
      this.o = acf.get_data(this.$el);
    },
    
    fetch : function () {
      var 
        self = this, 
        $field = this.$field;

      // Add class
      this.$el.addClass('is-loading');

      // Abort XHR if this field is already loading AJAX data
      if (this.o.xhr) {
        this.o.xhr.abort();
        this.o.xhr = false;
      }

      this.o.action = 'acf/fields/' + this.type + '/query';
      this.o.field_key = $field.data('key');
      this.o.post_id = acf.get('post_id');

      var ajax_data = acf.prepare_for_ajax(this.o);

      // Clear html if is new query
      if (ajax_data.paged == 1)
        this.$choices.children('.list').html('');

      // Add message
      this.$choices.find('ul:last').append('<p><i class="acf-loading"></i> ' + 
          acf._e('relationship', 'loading') + '</p>');

      // Results
      var xhr = $.ajax({
        url : acf.get('ajaxurl'),
        dataType : 'json',
        type : 'post',
        data : ajax_data,
        success : function (json) {
          self.set('$field', $field).render(json);
        }
      });

      // Update
      this.$el.data('xhr', xhr);
    }
  });

})(jQuery);
