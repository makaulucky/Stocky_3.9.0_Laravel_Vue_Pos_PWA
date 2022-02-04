<template>
  <div class="main-content">
    <breadcumb :page="$t('productsList')" :folder="$t('Products')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="products"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :select-options="{ 
          enabled: true ,
          clearSelectionText: '',
        }"
        @on-selected-rows-change="selectionChanged"
        :search-options="{
          enabled: true,
          placeholder: $t('Search_this_table'),  
        }"
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="tableOne vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="Product_PDF()" size="sm" variant="outline-success m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="Product_Excel()" size="sm" variant="outline-danger m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
          <b-button
            @click="Show_import_products()"
            size="sm"
            variant="info m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('product_import')"
          >
            <i class="i-Download"></i>
            {{ $t("import_products") }}
          </b-button>
          <router-link
            class="btn-sm btn btn-primary btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('products_add')"
            to="/app/products/store"
          >
            <span class="ul-btn__icon">
              <i class="i-Add"></i>
            </span>
            <span class="ul-btn__text ml-1">{{$t('Add')}}</span>
          </router-link>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <router-link
              v-if="currentUserPermissions && currentUserPermissions.includes('products_view')"
              v-b-tooltip.hover
              title="View"
              :to="{ name:'detail_product', params: { id: props.row.id} }"
            >
              <i class="i-Eye text-25 text-info"></i>
            </router-link>
            <router-link
              v-if="currentUserPermissions && currentUserPermissions.includes('products_edit')"
              v-b-tooltip.hover
              title="Edit"
              :to="{ name:'edit_product', params: { id: props.row.id } }"
            >
              <i class="i-Edit text-25 text-success"></i>
            </router-link>
            <a
              v-if="currentUserPermissions && currentUserPermissions.includes('products_delete')"
              @click="Remove_Product(props.row.id)"
              v-b-tooltip.hover
              title="Delete"
            >
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
          <span v-else-if="props.column.field == 'image'">
            <b-img
              thumbnail
              height="50"
              width="50"
              fluid
              :src="'/images/products/' + props.row.image"
              alt="image"
            ></b-img>
          </span>
        </template>
      </vue-good-table>

      <!-- Multiple filter -->
      <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
        <div class="px-3 py-2">
          <b-row>
            <!-- Code Product  -->
            <b-col md="12">
              <b-form-group :label="$t('CodeProduct')">
                <b-form-input
                  label="Code Product"
                  :placeholder="$t('SearchByCode')"
                  v-model="Filter_code"
                ></b-form-input>
              </b-form-group>
            </b-col>

            <!-- Name  -->
            <b-col md="12">
              <b-form-group :label="$t('ProductName')">
                <b-form-input
                  label="Name Product"
                  :placeholder="$t('SearchByName')"
                  v-model="Filter_name"
                ></b-form-input>
              </b-form-group>
            </b-col>

            <!-- Category  -->
            <b-col md="12">
              <b-form-group :label="$t('Categorie')">
                <v-select
                  :reduce="label => label.value"
                  :placeholder="$t('Choose_Category')"
                  v-model="Filter_category"
                  :options="categories.map(categories => ({label: categories.name, value: categories.id}))"
                />
              </b-form-group>
            </b-col>

            <!-- Brand  -->
            <b-col md="12">
              <b-form-group :label="$t('Brand')">
                <v-select
                  :reduce="label => label.value"
                  :placeholder="$t('Choose_Brand')"
                  v-model="Filter_brand"
                  :options="brands.map(brands => ({label: brands.name, value: brands.id}))"
                />
              </b-form-group>
            </b-col>

            <b-col md="6" sm="12">
              <b-button
                @click="Get_Products(serverParams.page)"
                variant="primary m-1"
                size="sm"
                block
              >
                <i class="i-Filter-2"></i>
                {{ $t("Filter") }}
              </b-button>
            </b-col>

            <b-col md="6" sm="12">
              <b-button @click="Reset_Filter()" variant="danger m-1" size="sm" block>
                <i class="i-Power-2"></i>
                {{ $t("Reset") }}
              </b-button>
            </b-col>
          </b-row>
        </div>
      </b-sidebar>

      <!-- Modal Show Import Products -->
      <b-modal
        ok-only
        ok-title="Cancel"
        size="md"
        id="importProducts"
        :title="$t('import_products')"
      >
        <b-form @submit.prevent="Submit_import" enctype="multipart/form-data">
          <b-row>
            <!-- File -->
            <b-col md="12" sm="12" class="mb-3">
              <b-form-group>
                <input type="file" @change="onFileSelected" label="Choose File">
                <b-form-invalid-feedback
                  id="File-feedback"
                  class="d-block"
                >{{$t('field_must_be_in_csv_format')}}</b-form-invalid-feedback>
              </b-form-group>
            </b-col>

             <b-col md="6" sm="12">
            <b-button type="submit" variant="primary" :disabled="ImportProcessing" size="sm" block>{{ $t("submit") }}</b-button>
              <div v-once class="typo__p" v-if="ImportProcessing">
                <div class="spinner sm spinner-primary mt-3"></div>
              </div>
          </b-col>

            <b-col md="6" sm="12">
              <b-button
                :href="'/import/exemples/import_products.csv'"
                variant="info"
                size="sm"
                block
              >{{ $t("Download_exemple") }}</b-button>
            </b-col>

            <b-col md="12" sm="12">
              <table class="table table-bordered table-sm mt-4">
                <tbody>
                  <tr>
                    <td>{{$t('Name_product')}}</td>
                    <th>
                      <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                    </th>
                  </tr>

                   <tr>
                    <td>{{$t('CodeProduct')}}</td>
                    <th>
                      <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                      {{$t('code_must_be_not_exist_already')}}
                    </th>
                  </tr>

                  <tr>
                    <td>{{$t('Categorie')}}</td>
                    <th>
                      <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                    </th>
                  </tr>

                  <tr>
                    <td>{{$t('ProductCost')}}</td>
                    <th>
                      <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                    </th>
                  </tr>

                  <tr>
                    <td>{{$t('ProductPrice')}}</td>
                    <th>
                      <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                    </th>
                  </tr>

                  <tr>
                    <td>{{$t('UnitProduct')}}</td>
                    <th>
                      <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                      {{$t('must_be_exist')}} {{$t('Please_use_short_name_of_unit')}}
                    </th>
                  </tr>

                  <tr>
                    <td>{{$t('Brand')}}</td>
                    <th>
                      <span class="badge badge-outline-info">{{$t('Field_optional')}}</span>
                    </th>
                  </tr>

                  <tr>
                    <td>{{$t('StockAlert')}}</td>
                    <th>
                      <span class="badge badge-outline-info">{{$t('Field_optional')}}</span>
                    </th>
                  </tr>

                  <tr>
                    <td>{{$t('Note')}}</td>
                    <th>
                      <span class="badge badge-outline-info">{{$t('Field_optional')}}</span>
                    </th>
                  </tr>
                </tbody>
              </table>
            </b-col>
          </b-row>
        </b-form>
      </b-modal>
    </div>
  </div>
</template>


<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";

export default {
  metaInfo: {
    title: "Products"
  },

  data() {
    return {
      serverParams: {
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      selectedIds: [],
      ImportProcessing:false,
      data: new FormData(),
      import_products: "",
      search: "",
      totalRows: "",
      isLoading: true,
      spinner: false,
      limit: "10",
      Filter_brand: "",
      Filter_code: "",
      Filter_name: "",
      Filter_category: "",
      categories: [],
      brands: [],
      products: {},
      warehouses: []
    };
  },

  computed: {
    ...mapGetters(["currentUserPermissions"]),
    columns() {
      return [
        {
          label: this.$t("image"),
          field: "image",
          type: "image",
          html: true,
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Name_product"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Code"),
          field: "code",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Categorie"),
          field: "category",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Brand"),
          field: "brand",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Price"),
          field: "price",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Unit"),
          field: "unit",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Quantity"),
          field: "quantity",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Action"),
          field: "actions",
          html: true,
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },
  methods: {
    //-------------------------------------- Products PDF ------------------------------\\
    Product_PDF() {
      var self = this;

      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "name", dataKey: "name" },
        { title: "code", dataKey: "code" },
        { title: "category", dataKey: "category" },
        { title: "brand", dataKey: "brand" },
        { title: "price", dataKey: "price" },
        { title: "unit", dataKey: "unit" },
        { title: "quantity", dataKey: "quantity" }
      ];
      pdf.autoTable(columns, self.products);
      pdf.text("Product List", 40, 25);
      pdf.save("Product_List.pdf");
    },

    //----------------------------------- Show import products -------------------------------\\
    Show_import_products() {
      this.$bvModal.show("importProducts");
    },

    //------------------------------ Event Import products -------------------------------\\
    onFileSelected(e) {
      this.import_products = "";
      let file = e.target.files[0];
      let errorFilesize;

      if (file["size"] < 1048576) {
        // 1 mega = 1,048,576 Bytes
        errorFilesize = false;
      } else {
        this.makeToast(
          "danger",
          this.$t("file_size_must_be_less_than_1_mega"),
          this.$t("Failed")
        );
      }

      if (errorFilesize === false) {
        this.import_products = file;
      }
    },

    //----------------------------------------Submit  import products-----------------\\
    Submit_import() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      var self = this;
      self.ImportProcessing = true;
      self.data.append("products", self.import_products);
      axios
        .post("Products/import/csv", self.data)
        .then(response => {
          self.ImportProcessing = false;
          if (response.data.status === true) {
            this.makeToast(
              "success",
              this.$t("Successfully_Imported"),
              this.$t("Success")
            );
            Fire.$emit("Event_import");
          } else if (response.data.status === false) {
            this.makeToast(
              "danger",
              this.$t("field_must_be_in_csv_format"),
              this.$t("Failed")
            );
          }
          // Complete the animation of theprogress bar.
          NProgress.done();
        })
        .catch(error => {
          self.ImportProcessing = false;
          // Complete the animation of theprogress bar.
          NProgress.done();
          this.makeToast(
            "danger",
            this.$t("Please_follow_the_import_instructions"),
            this.$t("Failed")
          );
        });
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //----Update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Products(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Products(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //---- Event Sort Change
    onSortChange(params) {
      let field = "";
      if (params[0].field == "brand") {
        field = "brand_id";
      } else if (params[0].field == "category") {
        field = "category_id";
      } else {
        field = params[0].field;
      }
      this.updateParams({
        sort: {
          type: params[0].type,
          field: field
        }
      });
      this.Get_Products(this.serverParams.page);
    },

    //---- Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Products(this.serverParams.page);
    },

    //------ Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_brand = "";
      this.Filter_code = "";
      this.Filter_name = "";
      this.Filter_category = "";
      this.Get_Products(this.serverParams.page);
    },

    //------------------------------------ Products Excel ------------------------------\\
    Product_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("Products/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "List_Products.xlsx");
          document.body.appendChild(link);
          link.click();
          // Complete the animation of theprogress bar.
          NProgress.done();
        })
        .catch(() => {
          // Complete the animation of theprogress bar.
          NProgress.done();
        });
    },

    // Simply replaces null values with strings=''
    setToStrings() {
      if (this.Filter_category === null) {
        this.Filter_category = "";
      } else if (this.Filter_brand === null) {
        this.Filter_brand = "";
      }
    },

    //----------------------------------- Get All Products ------------------------------\\
    Get_Products(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.setToStrings();
      axios
        .get(
          "Products?page=" +
            page +
            "&code=" +
            this.Filter_code +
            "&name=" +
            this.Filter_name +
            "&category_id=" +
            this.Filter_category +
            "&brand_id=" +
            this.Filter_brand +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )
        .then(response => {
          this.products = response.data.products;
          this.warehouses = response.data.warehouses;
          this.categories = response.data.categories;
          this.brands = response.data.brands;
          this.totalRows = response.data.totalRows;

          // Complete the animation of theprogress bar.
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //----------------------------------- Remove Product ------------------------------\\
    Remove_Product(id) {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          // Start the progress bar.
          NProgress.start();
          NProgress.set(0.1);
          axios
            .delete("Products/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.ProductDeleted"),
                "success"
              );

              Fire.$emit("Delete_Product");
            })
            .catch(() => {
              // Complete the animation of theprogress bar.
              setTimeout(() => NProgress.done(), 500);
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.AlreadyLinked"),
                "warning"
              );
            });
        }
      });
    },

    //---- Delete products by selection
    delete_by_selected() {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          // Start the progress bar.
          NProgress.start();
          NProgress.set(0.1);
          axios
            .post("Products/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.ProductDeleted"),
                "success"
              );

              Fire.$emit("Delete_Product");
            })
            .catch(() => {
              // Complete the animation of theprogress bar.
              setTimeout(() => NProgress.done(), 500);
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    }
  }, //end Methods

  //-----------------------------Created function-------------------

  created: function() {
    this.Get_Products(1);

    Fire.$on("Delete_Product", () => {
      this.Get_Products(this.serverParams.page);
      // Complete the animation of theprogress bar.
      setTimeout(() => NProgress.done(), 500);
    });

    Fire.$on("Event_import", () => {
      setTimeout(() => {
        this.Get_Products(this.serverParams.page);
        this.$bvModal.hide("importProducts");
      }, 500);
    });
  }
};
</script>