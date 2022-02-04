<template>
  <div class="main-content">
    <breadcumb :page="$t('ListAdjustments')" :folder="$t('Adjustment')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="adjustments"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
        enabled: true,
        placeholder: $t('Search_this_table'),  
      }"
        :select-options="{ 
          enabled: true ,
          clearSelectionText: '',
        }"
        @on-selected-rows-change="selectionChanged"
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="Adjustment_PDF()" size="sm" variant="outline-success m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="Adjustment_Excel()" size="sm" variant="outline-danger m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
          <router-link
            class="btn-sm btn btn-primary btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('adjustment_add')"
            to="/app/adjustments/store"
          >
            <span class="ul-btn__icon">
              <i class="i-Add"></i>
            </span>
            <span class="ul-btn__text ml-1">{{$t('Add')}}</span>
          </router-link>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a v-b-tooltip.hover title="View" @click="showDetails(props.row.id)">
              <i class="i-Eye text-25 text-info"></i>
            </a>
            <router-link
              v-if="currentUserPermissions && currentUserPermissions.includes('adjustment_edit')"
              v-b-tooltip.hover
              title="Edit"
              :to="'/app/adjustments/edit/'+props.row.id"
            >
              <i class="i-Edit text-25 text-success"></i>
            </router-link>
            <a
              v-b-tooltip.hover
              title="Delete"
              v-if="currentUserPermissions && currentUserPermissions.includes('adjustment_delete')"
              @click="Remove_Adjustment(props.row.id)"
            >
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </div>

    <!-- Multiple Filters -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- date  -->
          <b-col md="12">
            <b-form-group :label="$t('date')">
              <b-form-input type="date" v-model="Filter_date"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Reference  -->
          <b-col md="12">
            <b-form-group :label="$t('Reference')">
              <b-form-input label="Reference" :placeholder="$t('Reference')" v-model="Filter_Ref"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- warehouse  -->
          <b-col md="12">
            <b-form-group :label="$t('warehouse')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Warehouse')"
                v-model="Filter_warehouse"
                :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
              />
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button
              @click="Get_Adjustments(serverParams.page)"
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

    <!-- Show details -->
    <b-modal ok-only size="lg" id="showDetails" :title="$t('AdjustmentDetail')">
      <b-row>
        <b-col lg="5" md="12" sm="12" class="mt-3">
          <table class="table table-hover table-bordered table-sm">
            <tbody>
              <!-- date -->
              <tr>
                <td>{{$t('date')}}</td>
                <th>{{adjustment.date}}</th>
              </tr>
              <!-- Reference -->
              <tr>
                <td>{{$t('Reference')}}</td>
                <th>{{adjustment.Ref}}</th>
              </tr>
              <tr>
                <!-- warehouse -->
                <td>{{$t('warehouse')}}</td>
                <th>{{adjustment.warehouse}}</th>
              </tr>
            </tbody>
          </table>
        </b-col>
        <b-col lg="7" md="12" sm="12" class="mt-3">
          <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm">
              <thead>
                <tr>
                  <th scope="col">{{$t('ProductName')}}</th>
                  <th scope="col">{{$t('CodeProduct')}}</th>
                  <th scope="col">{{$t('Quantity')}}</th>
                  <th scope="col">{{$t('type')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="detail in details">
                  <td>{{detail.name}}</td>
                  <td>{{detail.code}}</td>
                  <td>{{formatNumber(detail.quantity ,2)}} {{detail.unit}}</td>
                  <td v-if="detail.type == 'add'">{{$t('Addition')}}</td>
                  <td v-else-if="detail.type == 'sub'">{{$t('Subtraction')}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </b-col>
      </b-row>
       <hr v-show="adjustment.note">
          <b-row class="mt-4">
           <b-col md="12">
             <p>{{adjustment.note}}</p>
           </b-col>
        </b-row>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";

export default {
  metaInfo: {
    title: "Adjustment"
  },
  data() {
    return {
      isLoading: true,
      serverParams: {
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      selectedIds: [],
      search: "",
      totalRows: "",
      limit: "10",
      Filter_date: "",
      Filter_Ref: "",
      Filter_warehouse: "",
      warehouses: [],
      adjustments: [],
      details: [],
      adjustment: {}
    };
  },

  computed: {
    ...mapGetters(["currentUserPermissions"]),
    columns() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("warehouse"),
          field: "warehouse_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("TotalProducts"),
          field: "items",
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
    //-------------------------------------- Adjustement PDF ------------------------------\\
    Adjustment_PDF() {
      var self = this;

      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Date", dataKey: "date" },
        { title: "Reference", dataKey: "Ref" },
        { title: "Warehouse", dataKey: "warehouse_name" },
        { title: "Total Products", dataKey: "items" }
      ];
      pdf.autoTable(columns, self.adjustments);
      pdf.text("Adjustment List", 40, 25);
      pdf.save("Adjustment_List.pdf");
    },

    //------------------------------------ Adjustement Excel ------------------------------\\
    Adjustment_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("adjustments/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "List_Adjustments.xlsx");
          document.body.appendChild(link);
          link.click();
          // Complete the animation of theprogress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(() => {
          // Complete the animation of theprogress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

    //---------------Get Details Adjustement ----------------------\\
    showDetails(id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("adjustments/detail/" + id)
        .then(response => {
          this.adjustment = response.data.adjustment;
          this.details = response.data.details;
          Fire.$emit("Get_Details_Adjust");
        })
        .catch(response => {
          Fire.$emit("Get_Details_Adjust");
        });
    },

    //------------------------------Formetted Numbers -------------------------\\
    formatNumber(number, dec) {
      const value = (typeof number === "string"
        ? number
        : number.toString()
      ).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec)
        return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    },

    //------  Update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Adjustments(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Adjustments(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //------ Event Sort change
    onSortChange(params) {
      let field = "";
      if (params[0].field == "warehouse_name") {
        field = "warehouse_id";
      } else {
        field = params[0].field;
      }
      this.updateParams({
        sort: {
          type: params[0].type,
          field: field
        }
      });
      this.Get_Adjustments(this.serverParams.page);
    },

    //------ Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Adjustments(this.serverParams.page);
    },

    //------ Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_date = "";
      this.Filter_Ref = "";
      this.Filter_warehouse = "";
      this.Get_Adjustments(this.serverParams.page);
    },

    // Simply replaces null values with strings=''
    setToStrings() {
      if (this.Filter_warehouse === null) {
        this.Filter_warehouse = "";
      }
    },

    //--------------------------------Get All Adjustements ----------------------\\
    Get_Adjustments(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.setToStrings();
      axios
        .get(
          "adjustments?page=" +
            page +
            "&Ref=" +
            this.Filter_Ref +
            "&warehouse_id=" +
            this.Filter_warehouse +
            "&date=" +
            this.Filter_date +
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
          this.adjustments = response.data.adjustments;
          this.warehouses = response.data.warehouses;
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

    //---------------------------------- Remove Adjustement----------------------\\
    Remove_Adjustment(id) {
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
            .delete("adjustments/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.AdjustDeleted"),
                "success"
              );

              Fire.$emit("Delete_Adjustment");
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
    },

    //---- Delete adjustments by selection
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
            .post("adjustments/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.AdjustDeleted"),
                "success"
              );

              Fire.$emit("Delete_Adjustment");
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
  },

  //----------------------------- Created function-------------------
  created: function() {
    this.Get_Adjustments(1);

    Fire.$on("Get_Details_Adjust", () => {
      // Complete the animation of theprogress bar.
      setTimeout(() => NProgress.done(), 500);
      this.$bvModal.show("showDetails");
    });

    Fire.$on("Delete_Adjustment", () => {
      setTimeout(() => {
        // Complete the animation of theprogress bar.
        NProgress.done();
        this.Get_Adjustments(this.serverParams.page);
      }, 500);
    });
  }
};
</script>