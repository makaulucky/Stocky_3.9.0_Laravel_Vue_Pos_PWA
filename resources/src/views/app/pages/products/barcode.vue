<template>
  <div class="main-content">
    <breadcumb :page="$t('Printbarcode')" :folder="$t('Products')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <!-- <b-card v-if="!isLoading"> -->
      <b-row v-if="!isLoading">
        <!-- Warehouse -->
        <b-col md="6" class="mb-3">
          <validation-observer ref="show_Barcode">
            <validation-provider name="warehouse" :rules="{ required: true}">
              <b-form-group slot-scope="{ valid, errors }" :label="$t('warehouse')">
                <v-select
                  :class="{'is-invalid': !!errors.length}"
                  :state="errors[0] ? false : (valid ? true : null)"
                  @input="Selected_Warehouse"
                  v-model="barcode.warehouse_id"
                  :reduce="label => label.value"
                  :placeholder="$t('Choose_Warehouse')"
                  :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
                />
                <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </validation-observer>
        </b-col>

         <!-- Product -->
                <b-col md="12" class="mb-3">
                  <h6>{{$t('ProductName')}}</h6>
                 
                  <div id="autocomplete" class="autocomplete">
                    <input 
                     :placeholder="$t('Scan_Search_Product_by_Code_Name')"
                      @keyup="search()" 
                      @focus="handleFocus"
                      @blur="handleBlur"
                      v-model="search_input"  
                      class="autocomplete-input" />
                    <ul class="autocomplete-result-list" v-show="focused">
                      <li class="autocomplete-result" v-for="product_fil in product_filter" @mousedown="SearchProduct(product_fil)">{{getResultValue(product_fil)}}</li>
                    </ul>
                </div>
                </b-col>

        <!-- Details Product  -->
        <b-col md="12">
          <div class="table-responsive">
            <table class="table table-hover table-md">
              <thead>
                <tr>
                  <th scope="col">{{$t('ProductName')}}</th>
                  <th scope="col">{{$t('CodeProduct')}}</th>
                  <th scope="col">{{$t('Quantity')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="product.code === ''">
                  <td colspan="3">{{$t('NodataAvailable')}}</td>
                </tr>
                <tr v-else>
                  <td>{{product.name}}</td>
                  <td>{{product.code}}</td>
                  <td>
                    <input
                      v-model.number="barcode.qte"
                      class="form-control w-50 h-25"
                      id="qte"
                      type="number"
                      name="qte"
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </b-col>

         <!-- Paper_size  -->
          <b-col md="12">
            <b-form-group :label="$t('Paper_size')">
              <v-select
                v-model="paper_size"
                @input="Selected_Paper_size"
                :reduce="label => label.value"
                :placeholder="$t('Paper_size')"
                :options="
                      [
                        {label: '40 per sheet (a4) (1.799 * 1.003)', value: 'style40'},
                        {label: '30 per sheet (2.625 * 1)', value: 'style30'},
                        {label: '24 per sheet (a4) (2.48 * 1.334)', value: 'style24'},
                        {label: '20 per sheet (4 * 1)', value: 'style20'},
                        {label: '18 per sheet (a4) (2.5 * 1.835)', value: 'style18'},
                        {label: '14 per sheet (4 * 1.33)', value: 'style14'},
                        {label: '12 per sheet (a4) (2.5 * 2.834)', value: 'style12'},
                        {label: '10 per sheet (4 * 2)', value: 'style10'},

                      ]"
              ></v-select>
            </b-form-group>
          </b-col>
        <b-col md="12">
          <button @click="submit()" type="submit" class="btn btn-primary btn-sm m-1">
            <i class="i-Edit"></i>
            {{$t('Updat')}}
          </button>
          <button @click="reset()" type="submit" class="btn btn-danger btn-sm m-1">
            <i class="i-Power-2"></i>
            {{$t('Reset')}}
          </button>
          <button
            @click="print_Bracode('print_barcode')"
            value="Print"
            type="submit"
            class="btn btn-light btn-sm pull-right m-1"
          >
            <i class="i-Billing"></i>
            {{$t('print')}}
          </button>
        </b-col>
      
   
            <b-col md="12">
              <div class="barcode-row" v-if="ShowCard" id="print_barcode_label">
                <div :class="class_type_page" v-for ="(k, i) in total_a4" :key="i">
                  <div class="barcode-item" :class="class_sheet"  v-for="(sheet, index) in sheets" :key="index" >
                      <span class="barcode-name">{{product.name}}</span>
                    <barcode
                      class="barcode"
                      :format="product.Type_barcode"
                      :value="product.barcode"
                      textmargin="0"
                      fontoptions="bold"
                      fontSize= "15"
                      height= "25"
                      width= "1"
                    ></barcode>
                    </div>
                </div>
                <div :class="class_type_page"  v-if="rest > 0">
                  <div class="barcode-item" :class="class_sheet"  v-for="(sheet, index) in rest" :key="index" >
                      <span  class="barcode-name">{{product.name}}</span>
                    <barcode
                      class="barcode"
                      fontSize= "15"
                      :format="product.Type_barcode"
                      :value="product.barcode"
                      textmargin="0"
                      fontoptions="bold"
                      height= "25"
                      width= "1"
                    ></barcode>
                    </div>
                </div>
              </div>
            </b-col>
          </b-row>
          </div>
      
</template>

<script>
import VueBarcode from "vue-barcode";
import NProgress from "nprogress";

export default {
  components: {
    barcode: VueBarcode
  },
  data() {
    return {
      focused: false,
      timer:null,
      search_input:'',
      product_filter:[],
      isLoading: true,
      ShowCard: false,
      barcode: {
        product_id: "",
        warehouse_id: "",
        qte: 10
      },
      count: "",
      paper_size:"",
      sheets:'',
      total_a4:'',
      class_sheet:'',
      class_type_page:'',
      rest:'',     
      warehouses: [],
      submitStatus: null,
      products: [],
      product: {
        name: "",
        code: "",
        Type_barcode: "",
        barcode:"",
      }
    };
  },

  methods: {


    Per_Page(){
      this.total_a4 = parseInt(this.barcode.qte/this.sheets);
      this.rest = this.barcode.qte%this.sheets;
    },

 //---------------------- Event Selected_Paper_size------------------------------\\
    Selected_Paper_size(value) {
      if(value == 'style40'){
        this.sheets = 40;
        this.class_sheet = 'style40';
        this.class_type_page = 'barcodea4';
      }else if(value == 'style30'){
        this.sheets = 30;
        this.class_type_page = 'barcode_non_a4';
        this.class_sheet = 'style30';
      }else if(value == 'style24'){
        this.sheets = 24;
        this.class_sheet = 'style24';
       this.class_type_page = 'barcodea4';
      }else if(value == 'style20'){
        this.sheets = 20;
        this.class_sheet = 'style20';
        this.class_type_page = 'barcode_non_a4';
      }else if(value == 'style18'){
        this.sheets =  18;
        this.class_sheet = 'style18';
        this.class_type_page = 'barcodea4';
      }else if(value == 'style14'){
        this.sheets = 14;
        this.class_sheet = 'style14';
        this.class_type_page = 'barcode_non_a4';
      }else if(value == 'style12'){
        this.sheets = 12;
        this.class_sheet = 'style12';
       this.class_type_page = 'barcodea4';
      }else if(value == 'style10'){
        this.sheets = 10;
        this.class_sheet = 'style10';
       this.class_type_page = 'barcode_non_a4';
      }
     
      this.Per_Page();
    },


    //------ Validate Form
    submit() {
      this.$refs.show_Barcode.validate().then(success => {
        if (!success) {
          return;
        } else {
         
          this.showBarcode();
          // this.Per_Page();
        }
      });
    },

    //---Validate State Fields
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

      handleFocus() {
      this.focused = true
    },

    handleBlur() {
      this.focused = false
    },
    

   // Search Products
    search(){

      if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
      }

      if (this.search_input.length < 1) {

        return this.product_filter= [];
      }
      if (this.barcode.warehouse_id != "" &&  this.barcode.warehouse_id != null) {
         this.timer = setTimeout(() => {
        
         this.product_filter = this.products.filter(product => {
            return (
                product.name.toLowerCase().includes(this.search_input.toLowerCase()) ||
                product.code.toLowerCase().includes(this.search_input.toLowerCase()) ||
                product.barcode.toLowerCase().includes(this.search_input.toLowerCase())
                );
              });
        }, 800);
      } else {
        this.makeToast(
          "warning",
          this.$t("SelectWarehouse"),
          this.$t("Warning")
        );
      }


    },

    //------ Search Result value
    getResultValue(result) {
      return result.code + " " + "(" + result.name + ")";
    },

    //------ Submit Search Product
    SearchProduct(result) {
      this.product = {};
      if (this.product.code === result.code) {
        this.makeToast("warning", this.$t("AlreadyAdd"), this.$t("Warning"));
      } else {
        this.product.code = result.code;
        this.product.barcode = result.barcode;
        this.product.name = result.name;
        this.product.Type_barcode = result.Type_barcode;
      }

      this.search_input= '';
      this.product_filter = [];
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------------------------------------ Get Products By Warehouse -------------------------\\

    Get_Products_By_Warehouse(id) {
      // Start the progress bar.
        NProgress.start();
        NProgress.set(0.1);
      axios
        .get("Products/Warehouse/" + id + "?stock=" + 0)
         .then(response => {
            this.products = response.data;
             NProgress.done();

            })
          .catch(error => {
          });
    },

    //-------------------------------------- Print Barcode -------------------------\\

    print_Bracode() {
      var divContents = document.getElementById("print_barcode_label").innerHTML;
      var a = window.open("", "", "height=500, width=500");
      a.document.write(
        '<link rel="stylesheet" href="/assets_setup/css/print_label.css"><html>'
      );
      a.document.write("<body >");
      a.document.write(divContents);
      a.document.write("</body></html>");
      a.document.close();
      a.print();
    },
   
    //-------------------------------------- Show Barcode -------------------------\\
    showBarcode() {
      this.Per_Page();
      this.count = this.barcode.qte;
      this.ShowCard = true;
    },

    //---------------------- Event Select Warehouse ------------------------------\\
    Selected_Warehouse(value) {
      this.search_input= '';
      this.product_filter = [];
      this.Get_Products_By_Warehouse(value);
    },

    //----------------------------------- GET Barcode Elements -------------------------\\
    Get_Elements: function() {
      axios
        .get("Products/Get_element/barcode")
        .then(response => {
          this.warehouses = response.data.warehouses;
          this.isLoading = false;
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //----------------------------------- Reset Data -------------------------\\
    reset() {
      this.ShowCard = false;
      this.products = [];
      this.product.name = "";
      this.product.code = "";
      this.barcode.qte = 10;
      this.count = 10;
      this.barcode.warehouse_id = "";
      this.search_input= '';
      this.product_filter = [];
    }
  }, //end Methods

  //-----------------------------Created function-------------------

  created: function() {
    this.Get_Elements();
  }
};
</script>