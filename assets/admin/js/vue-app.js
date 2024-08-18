let otw_woo_ring_builder_vue_app_diamond_api = Vue.createApp({
  data: function(){
    return{
      greetings: "Hello from vue3",
      displayLogs:false,
      data:{}
    }
  },
  methods:{
    toggleLogs(e){
      //jQuery(e.target).next().attr('v-if', e.target.checked);
      // console.log(this.php_execution_time);
    },
  },
  mounted: function() {
    this.data = otw_woo_ring_builder_vue_app_diamond_api_svalues;
  }
});
console.log(otw_woo_ring_builder_vue_app_diamond_api_svalues);
otw_woo_ring_builder_vue_app_diamond_api.mount('#otw_woo_ring_builder_vue_app_diamond_api');