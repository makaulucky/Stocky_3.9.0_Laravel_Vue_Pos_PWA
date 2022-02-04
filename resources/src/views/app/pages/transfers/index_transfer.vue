<template>
  <div class="main-content">
    <breadcumb :page="$t('ListTransfers')" :folder="$t('StockTransfers')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="transfers"
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
        styleClass="tableOne table-hover vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info ripple m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="Transfer_PDF()" size="sm" variant="outline-success ripple m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="Transfer_Excel()" size="sm" variant="outline-danger ripple m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
          <router-link
            class="btn-sm btn btn-primary ripple btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('transfer_add')"
            to="/app/transfers/store"
          >
            <span class="ul-btn__icon">
              <i class="i-Add"></i>
            </span>
            <span class="ul-btn__text ml-1">{{$t('Add')}}</span>
          </router-link>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a title="View" v-b-tooltip.hover @click="showDetails(props.row.id)">
              <i class="i-Eye text-25 text-info"></i>
            </a>
            <router-link
              v-if="currentUserPermissions && currentUserPermissions.includes('transfer_edit')"
              title="Edit"
              v-b-tooltip.hover
              :to="{ name:'edit_transfer', params: { id: props.row.id } }"
            >
              <i class="i-Edit text-25 text-success"></i>
            </router-link>
            <a
              title="Delete"
              v-b-tooltip.hover
              v-if="currentUserPermissions && currentUserPermissions.includes('transfer_delete')"
              @click="Remove_Transfer(props.row.id)"
            >
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
          <div v-else-if="props.column.field == 'statut'">
            <span
              v-if="props.row.statut == 'completed'"
              class="badge badge-outline-success"
            >{{$t('complete')}}</span>
            <span
              v-else-if="props.row.statut == 'sent'"
              class="badge badge-outline-warning"
            >{{$t('Sent')}}</span>
            <span v-else class="badge badge-outline-danger">{{$t('Pending')}}</span>
          </div>
        </template>
      </vue-good-table>
    </div>

    <!-- multiple filters -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- Reference  -->
          <b-col md="12">
            <b-form-group :label="$t('Reference')">
              <b-form-input label="Reference" :placeholder="$t('Reference')" v-model="Filter_Ref"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- From warehouse  -->
          <b-col md="12">
            <b-form-group :label="$t('FromWarehouse')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Warehouse')"
                v-model="Filter_From"
                :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
              />
            </b-form-group>
          </b-col>

          <!-- To warehouse  -->
          <b-col md="12">
            <b-form-group :label="$t('ToWarehouse')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Warehouse')"
                v-model="Filter_To"
                :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
              />
            </b-form-group>
          </b-col>

          <!-- Status  -->
          <b-col md="12">
            <b-form-group :label="$t('Status')">
              <v-select
                v-model="Filter_status"
                :reduce="label => label.value"
                :placeholder="$t('Choose_Status')"
                :options="
                      [
                        {label: 'Completed', value: 'completed'},
                        {label: 'Sent', value: 'sent'},
                        {label: 'Pending', value: 'pending'},
                      ]"
              ></v-select>
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button
              @click="Get_Transfers(serverParams.page)"
              variant="primary ripple m-1"
              size="sm"
              block
            >
              <i class="i-Filter-2"></i>
              {{ $t("Filter") }}
            </b-button>
          </b-col>
          <b-col md="6" sm="12">
            <b-button @click="Reset_Filter()" variant="danger ripple m-1" size="sm" block>
              <i class="i-Power-2"></i>
              {{ $t("Reset") }}
            </b-button>
          </b-col>
        </b-row>
      </div>
    </b-sidebar>

    <!-- Transfer Details -->
    <b-modal ok-only size="lg" id="showDetails" :title="$t('TransferDetail')">
      <b-row>
        <b-col lg="5" md="12" sm="12" class="mt-3">
          <table class="table table-hover table-bordered table-sm">
            <tbody>
              <!-- date -->
              <tr>
                <td>{{$t('date')}}</td>
                <th>{{transfer.date}}</th>
              </tr>
              <!-- Reference -->
              <tr>
                <td>{{$t('Reference')}}</td>
                <th>{{transfer.Ref}}</th>
              </tr>
              <!-- From warehouse -->
              <tr>
                <td>{{$t('FromWarehouse')}}</td>
                <th>{{transfer.from_warehouse}}</th>
              </tr>
              <!-- To warehouse -->
              <tr>
                <td>{{$t('ToWarehouse')}}</td>
                <th>{{transfer.to_warehouse}}</th>
              </tr>
              <!-- Grand Total -->
              <tr>
                <td>{{$t('Total')}}</td>
                <th>{{currentUser.currency}}{{formatNumber(transfer.GrandTotal ,2)}}</th>
              </tr>
              <!-- Status -->
              <tr>
                <td>{{$t('Status')}}</td>
                <th>
                  <span
                    v-if="transfer.statut == 'completed'"
                    class="badge badge-outline-success"
                  >{{$t('complete')}}</span>
                  <span
                    v-else-if="transfer.statut == 'sent'"
                    class="badge badge-outline-warning"
                  >{{$t('Sent')}}</span>
                  <span v-else class="badge badge-outline-danger">{{$t('Pending')}}</span>
                </th>
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
                  <th scope="col">{{$t('SubTotal')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="detail in details">
                  <td>{{detail.name}}</td>
                  <td>{{detail.code}}</td>
                  <td>{{formatNumber(detail.quantity ,2)}} {{detail.unit}}</td>
                  <td>{{currentUser.currency}} {{detail.total.toFixed(2)}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </b-col>
      </b-row>
         <hr v-show="transfer.note">
          <b-row class="mt-4">
           <b-col md="12">
             <p>{{transfer.note}}</p>
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
    title: "Transfer"
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
      loading: true,
      spinner: false,
      limit: "10",
      Filter_date: "",
      Filter_status: "",
      Filter_Ref: "",
      Filter_From: "",
      Filter_To: "",
      details: [],
      warehouses: [],
      transfers: [],
      transfer: {
        GrandTotal: ""
      }
    };
  },
  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),
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
          label: this.$t("FromWarehouse"),
          field: "from_warehouse",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("ToWarehouse"),
          field: "to_warehouse",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Items"),
          field: "items",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Total"),
          field: "GrandTotal",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Status"),
          field: "statut",
          html: true,
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
    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Transfers(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Transfers(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //---- Event sort change

    onSortChange(params) {
      let field = "";
      if (params[0].field == "from_warehouse") {
        field = "from_warehouse_id";
      } else if (params[0].field == "to_warehouse") {
        field = "to_warehouse_id";
      } else {
        field = params[0].field;
      }
      this.updateParams({
        sort: {
          type: params[0].type,
          field: field
        }
      });
      this.Get_Transfers(this.serverParams.page);
    },

    //---- Event on Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Transfers(this.serverParams.page);
    },

    setToStrings() {
      // Simply replaces null values with strings=''
      if (this.Filter_From === null) {
        this.Filter_From = "";
      } else if (this.Filter_To === null) {
        this.Filter_To = "";
      } else if (this.Filter_status === null) {
        this.Filter_status = "";
      }
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

    //------ Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_date = "";
      this.Filter_status = "";
      this.Filter_Ref = "";
      this.Filter_From = "";
      this.Filter_To = "";
      this.Get_Transfers(this.serverParams.page);
    },

    //--------------------------------Get All Transfers ----------------------\\
    Get_Transfers(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.setToStrings();
      axios
        .get(
          "transfers?page=" +
            page +
            "&Ref=" +
            this.Filter_Ref +
            "&statut=" +
            this.Filter_status +
            "&from_warehouse_id=" +
            this.Filter_From +
            "&to_warehouse_id=" +
            this.Filter_To +
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
          this.transfers = response.data.transfers;
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

    //----------------------------------- Get Details Transfer ------------------------------\\
    showDetails(id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("transfers/" + id)
        .then(response => {
          this.transfer = response.data.transfer;
          this.details = response.data.details;
          Fire.$emit("Get_Details_Transfer");
        })
        .catch(response => {
          Fire.$emit("Get_Details_Transfer");
        });
    },

    //-------------------------------------- Transfer PDF ------------------------------\\
    Transfer_PDF() {
      var self = this;

      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Ref", dataKey: "Ref" },
        { title: "From warehouse", dataKey: "from_warehouse" },
        { title: "To warehouse", dataKey: "to_warehouse" },
        { title: "Total Products", dataKey: "items" },
        { title: "Status", dataKey: "statut" }
      ];
      pdf.autoTable(columns, self.transfers);
      pdf.text("Transfer List", 40, 25);
      pdf.save("Transfer_List.pdf");
    },

    //------------------------------------ Transfer Excel ------------------------------\\
    Transfer_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("transfers/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "List_Transfers.xlsx");
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

    //---------------------------------- Delete Transfer ----------------------\\
    Remove_Transfer(id) {
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
            .delete("transfers/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.TitleTransfer"),
                "success"
              );

              Fire.$emit("Delete_Transfer");
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

    //---- Delete transfers by selection

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
            .post("transfers/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.TitleTransfer"),
                "success"
              );

              Fire.$emit("Delete_Transfer");
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

  //-----------------------------Autoload function-------------------
  created: function() {
    this.Get_Transfers(1);

    Fire.$on("Get_Details_Transfer", () => {
      this.$bvModal.show("showDetails");
      // Complete the animation of theprogress bar.
      setTimeout(() => NProgress.done(), 500);
    });

    Fire.$on("Delete_Transfer", () => {
      setTimeout(() => {
        this.Get_Transfers(this.serverParams.page);
        // Complete the animation of theprogress bar.
        setTimeout(() => NProgress.done(), 500);
      }, 500);
    });
  }
};
</script>