<template>
  <div class="main-content">
    <breadcumb :page="$t('ListReturns')" :folder="$t('PurchasesReturn')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="purchase_returns"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
          placeholder: $t('Search_this_table'),
          enabled: true,
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
        :styleClass="showDropdown?'tableOne table-hover vgt-table full-height':'tableOne table-hover vgt-table non-height'"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info ripple m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="Returns_Purchase_PDF()" size="sm" variant="outline-success ripple m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="Returns_Purchase_Excel()" size="sm" variant="outline-danger ripple m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
          <router-link
            class="btn-sm btn btn-primary ripple btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('Purchase_Returns_add')"
            to="/app/purchase_return/store"
          >
            <span class="ul-btn__icon">
              <i class="i-Add"></i>
            </span>
            <span class="ul-btn__text ml-1">{{$t('Add')}}</span>
          </router-link>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <div>
              <b-dropdown
                id="dropdown-left"
                variant="link"
                text="Left align"
                toggle-class="text-decoration-none"
                size="lg"
                no-caret
              >
                <template v-slot:button-content class="_r_btn border-0">
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                </template>
                <b-navbar-nav>
                  <b-dropdown-item title="Show" :to="'/app/purchase_return/detail/'+props.row.id">
                    <i class="nav-icon i-Eye font-weight-bold mr-2"></i>
                    {{$t('ReturnDetail')}}
                  </b-dropdown-item>
                </b-navbar-nav>

                <b-dropdown-item
                  title="Edit"
                  v-if="currentUserPermissions.includes('Purchase_Returns_edit')"
                  :to="'/app/purchase_return/edit/'+props.row.id"
                >
                  <i class="nav-icon i-Pen-2 font-weight-bold mr-2"></i>
                  {{$t('EditReturn')}}
                </b-dropdown-item>

                <b-dropdown-item
                  v-if="currentUserPermissions.includes('payment_returns_view')"
                  @click="Show_Payments(props.row.id , props.row)"
                >
                  <i class="nav-icon i-Money-Bag font-weight-bold mr-2"></i>
                  {{$t('ShowPayment')}}
                </b-dropdown-item>

                <b-dropdown-item
                  v-if="currentUserPermissions.includes('payment_returns_add')"
                  @click="New_Payment(props.row)"
                >
                  <i class="nav-icon i-Add font-weight-bold mr-2"></i>
                  {{$t('AddPayment')}}
                </b-dropdown-item>

                <b-dropdown-item title="PDF" @click="Return_PDF(props.row , props.row.id)">
                  <i class="nav-icon i-File-TXT font-weight-bold mr-2"></i>
                  {{$t('DownloadPdf')}}
                </b-dropdown-item>

                <b-dropdown-item
                  title="Email"
                  @click="Return_Purchase_Email(props.row , props.row.id)"
                >
                  <i class="nav-icon i-Envelope-2 font-weight-bold mr-2"></i>
                  {{$t('EmailReturn')}}
                </b-dropdown-item>

                <b-dropdown-item
                  title="Delete"
                  v-if="currentUserPermissions.includes('Purchase_Returns_delete')"
                  @click="Remove_Return(props.row.id)"
                >
                  <i class="nav-icon i-Close-Window font-weight-bold mr-2"></i>
                  {{$t('DeleteReturn')}}
                </b-dropdown-item>
              </b-dropdown>
            </div>
          </span>
          <div v-else-if="props.column.field == 'statut'">
            <span
              v-if="props.row.statut == 'completed'"
              class="badge badge-outline-success"
            >{{$t('complete')}}</span>
            <span v-else class="badge badge-outline-info">{{$t('Pending')}}</span>
          </div>

          <div v-else-if="props.column.field == 'payment_status'">
            <span
              v-if="props.row.payment_status == 'paid'"
              class="badge badge-outline-success"
            >{{$t('Paid')}}</span>
            <span
              v-else-if="props.row.payment_status == 'partial'"
              class="badge badge-outline-primary"
            >{{$t('partial')}}</span>
            <span v-else class="badge badge-outline-warning">{{$t('Unpaid')}}</span>
          </div>
        </template>
      </vue-good-table>
    </div>

    <!-- Sidebar Filter -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- date  -->
          <b-col md="12">
            <b-form-group :label="$t('date')">
              <b-form-input type="date" v-model="Filter_date"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Reference -->
          <b-col md="12">
            <b-form-group :label="$t('Reference')">
              <b-form-input label="Reference" :placeholder="$t('Reference')" v-model="Filter_Ref"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Supplier  -->
          <b-col md="12">
            <b-form-group :label="$t('Supplier')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Supplier')"
                v-model="Filter_Supplier"
                :options="suppliers.map(suppliers => ({label: suppliers.name, value: suppliers.id}))"
              />
            </b-form-group>
          </b-col>

          <!-- warehouse -->
          <b-col md="12">
            <b-form-group :label="$t('warehouse')">
              <v-select
                v-model="Filter_warehouse"
                :reduce="label => label.value"
                :placeholder="$t('Choose_Warehouse')"
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
                        {label: 'completed', value: 'completed'},
                        {label: 'Pending', value: 'pending'},
                      ]"
              ></v-select>
            </b-form-group>
          </b-col>

          <!-- Payment Status  -->
          <b-col md="12">
            <b-form-group :label="$t('PaymentStatus')">
              <v-select
                v-model="Filter_Payment"
                :reduce="label => label.value"
                :placeholder="$t('Choose_Status')"
                :options="
                      [
                        {label: 'Paid', value: 'paid'},
                        {label: 'partial', value: 'partial'},
                        {label: 'UnPaid', value: 'unpaid'},
                      ]"
              ></v-select>
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button
              @click="Get_purchase_returns(serverParams.page)"
              variant="primary ripple m-1"
              size="sm"
            >
              <i class="i-Filter-2"></i>
              {{ $t("Filter") }}
            </b-button>
          </b-col>
          <b-col md="6" sm="12">
            <b-button @click="Reset_Filter()" variant="danger ripple m-1" size="sm">
              <i class="i-Power-2"></i>
              {{ $t("Reset") }}
            </b-button>
          </b-col>
        </b-row>
      </div>
    </b-sidebar>

    <!-- Modal Show Payments-->
    <b-modal hide-footer size="lg" id="Show_payment" :title="$t('ShowPayment')">
      <b-row>
        <b-col lg="12" md="12" sm="12" class="mt-3">
          <div class="table-responsive">
            <table class="table table-hover table-bordered table-md">
              <thead>
                <tr>
                  <th scope="col">{{$t('date')}}</th>
                  <th scope="col">{{$t('Reference')}}</th>
                  <th scope="col">{{$t('Amount')}}</th>
                  <th scope="col">{{$t('PayeBy')}}</th>
                  <th scope="col">{{$t('Action')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="factures.length <= 0">
                  <td colspan="5">{{$t('NodataAvailable')}}</td>
                </tr>
                <tr v-for="facture in factures">
                  <td>{{facture.date}}</td>
                  <td>{{facture.Ref}}</td>
                  <td>{{currentUser.currency}} {{formatNumber((facture.montant),2)}}</td>
                  <td>{{facture.Reglement}}</td>
                  <td>
                    <div role="group" aria-label="Basic example" class="btn-group">
                      <span
                        title="Print"
                        class="btn btn-icon btn-info btn-sm"
                        @click="Payment_Return_PDF(facture,facture.id)"
                      >
                        <i class="i-Billing"></i>
                      </span>
                      <span
                        v-if="currentUserPermissions.includes('payment_returns_edit')"
                        title="Edit"
                        class="btn btn-icon btn-success btn-sm"
                        @click="Edit_Payment(facture)"
                      >
                        <i class="i-Pen-2"></i>
                      </span>
                      <span
                        title="Email"
                        class="btn btn-icon btn-primary btn-sm"
                        @click="EmailPayment(facture , purchase_return)"
                      >
                        <i class="i-Envelope"></i>
                      </span>
                       <span
                        title="SMS"
                        class="btn btn-icon btn-secondary btn-sm"
                        @click="Payment_PurchaseReturn_SMS(facture)"
                      >
                        <i class="i-Speach-Bubble"></i>
                      </span>
                      <span
                        v-if="currentUserPermissions.includes('payment_returns_delete')"
                        title="Delete"
                        class="btn btn-icon btn-danger btn-sm"
                        @click="Remove_Payment(facture.id)"
                      >
                        <i class="i-Close"></i>
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </b-col>
      </b-row>
    </b-modal>
    <!-- Modal Add Payment-->
    <validation-observer ref="Add_payment">
      <b-modal
        hide-footer
        size="lg"
        id="Add_Payment"
        :title="EditPaiementMode?$t('EditPayment'):$t('AddPayment')"
      >
        <b-form @submit.prevent="Submit_Payment">
          <b-row>
            <!-- date -->
            <b-col lg="6" md="12" sm="12">
              <validation-provider
                name="date"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('date')">
                  <b-form-input
                    label="date"
                    :state="getValidationState(validationContext)"
                    aria-describedby="date-feedback"
                    v-model="facture.date"
                    type="date"
                  ></b-form-input>
                  <b-form-invalid-feedback id="date-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Reference  -->
            <b-col lg="6" md="12" sm="12">
              <b-form-group :label="$t('Reference')">
                <b-form-input
                  disabled="disabled"
                  label="Reference"
                  :placeholder="$t('Reference')"
                  v-model="facture.Ref"
                ></b-form-input>
              </b-form-group>
            </b-col>

             <!-- Received  Amount  -->
            <b-col lg="6" md="12" sm="12">
              <validation-provider
                name="Received Amount"
                :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                v-slot="validationContext"
              >
              <b-form-group :label="$t('Received_Amount')">
                <b-form-input
                  @keyup="Verified_Received_Amount(facture.received_amount)"
                  label="Received_Amount"
                  :placeholder="$t('Received_Amount')"
                  v-model.number="facture.received_amount"
                  :state="getValidationState(validationContext)"
                  aria-describedby="Received_Amount-feedback"
                ></b-form-input>
                <b-form-invalid-feedback
                  id="Received_Amount-feedback"
                >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>

            <!-- Paying Amount  -->
            <b-col lg="6" md="12" sm="12">
              <validation-provider
                name="Amount"
                :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Paying_Amount')">
                  <b-form-input
                   @keyup="Verified_paidAmount(facture.montant)"
                    label="Amount"
                    :placeholder="$t('Paying_Amount')"
                    v-model.number="facture.montant"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Amount-feedback"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Amount-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- change Amount  -->
            <b-col lg="6" md="12" sm="12">
              <label>{{$t('Change')}} :</label>
              <p
                class="change_amount"
              >{{parseFloat(facture.received_amount - facture.montant).toFixed(2)}}</p>
            </b-col>

            <!-- Payment choice -->
            <b-col lg="6" md="12" sm="12">
              <validation-provider name="Payment choice" :rules="{ required: true}">
                <b-form-group slot-scope="{ valid, errors }" :label="$t('Paymentchoice')">
                  <v-select
                    :class="{'is-invalid': !!errors.length}"
                    :state="errors[0] ? false : (valid ? true : null)"
                    v-model="facture.Reglement"
                    @input="Selected_PaymentMethod"
                    :reduce="label => label.value"
                    :placeholder="$t('PleaseSelect')"
                    :options="
                          [
                          {label: 'Cash', value: 'Cash'},
                          {label: 'credit card', value: 'credit card'},
                          {label: 'cheque', value: 'cheque'},
                          {label: 'Western Union', value: 'Western Union'},
                          {label: 'bank transfer', value: 'bank transfer'},
                          {label: 'other', value: 'other'},
                          ]"
                  ></v-select>
                  <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Note -->
            <b-col lg="12" md="12" sm="12" class="mt-3">
              <b-form-group :label="$t('Note')">
                <b-form-textarea id="textarea" v-model="facture.notes" rows="3" max-rows="6"></b-form-textarea>
              </b-form-group>
            </b-col>

             <b-col md="12" class="mt-3">
              <b-button
                variant="primary"
                type="submit"
                :disabled="paymentProcessing"
              >{{$t('submit')}}</b-button>
              <div v-once class="typo__p" v-if="paymentProcessing">
                <div class="spinner sm spinner-primary mt-3"></div>
              </div>
            </b-col>
          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";

export default {
  metaInfo: {
    title: "Return Purchase"
  },

  data() {
    return {
      paymentProcessing: false,
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
      submitStatus: null,
      showDropdown: false,
      EditPaiementMode: false,
      Filter_Supplier: "",
      Filter_status: "",
      Filter_Payment: "",
      Filter_Ref: "",
      Filter_date: "",
      Filter_warehouse: "",
      purchase_returns: [],
      purchase_return: {},
      suppliers: [],
      warehouses: [],
      purchase_return_id: "",
      factures: [],
      purchase_return_due:'',
      due:0,
      limit: "10",
      facture: {
        id: "",
        purchase_return_id: "",
        date: "",
        Ref: "",
        montant: "",
        received_amount: "",
        Reglement: "",
        notes: ""
      },
      email: {
        to: "",
        subject: "",
        message: ""
      },

      emailPayment: {
        id: "",
        to: "",
        subject: "",
        message: "",
        client_name: "",
        Ref: ""
      }
    };
  },

   mounted() {
    this.$root.$on("bv::dropdown::show", bvEvent => {
      this.showDropdown = true;
    });
    this.$root.$on("bv::dropdown::hide", bvEvent => {
      this.showDropdown = false;
    });
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
          label: this.$t("Supplier"),
          field: "provider_name",
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
          label: this.$t("Status"),
          field: "statut",
          html: true,
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Total"),
          field: "GrandTotal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Paid"),
          field: "paid_amount",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Due"),
          field: "due",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("PaymentStatus"),
          field: "payment_status",
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

    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_purchase_returns(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_purchase_returns(1);
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
      if (params[0].field == "provider_name") {
        field = "provider_id";
      } else if (params[0].field == "warehouse_name") {
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
      this.Get_purchase_returns(this.serverParams.page);
    },

    //---- Event Search

    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_purchase_returns(this.serverParams.page);
    },

    //------ Validate Form Submit_Payment
    Submit_Payment() {
      this.$refs.Add_payment.validate().then(success => {
        if (!success) {
          return;
        } else if (this.facture.montant > this.facture.received_amount) {
          this.makeToast(
            "warning",
            this.$t("Paying_amount_is_greater_than_Received_amount"),
            this.$t("Warning")
          );
          this.facture.received_amount = 0;
        }
        else if (this.facture.montant > this.due) {
          this.makeToast(
            "warning",
            this.$t("Paying_amount_is_greater_than_Grand_Total"),
            this.$t("Warning")
          );
          this.facture.montant = 0;

        }else if (!this.EditPaiementMode) {
            this.Create_Payment();
        } else {
            this.Update_Payment();
        }

      });
    },

      //---------- keyup paid Amount

    Verified_paidAmount() {
      if (isNaN(this.facture.montant)) {
        this.facture.montant = 0;
      } else if (this.facture.montant > this.facture.received_amount) {
        this.makeToast(
          "warning",
          this.$t("Paying_amount_is_greater_than_Received_amount"),
          this.$t("Warning")
        );
        this.facture.montant = 0;
      } 
      else if (this.facture.montant > this.due) {
        this.makeToast(
          "warning",
          this.$t("Paying_amount_is_greater_than_Grand_Total"),
          this.$t("Warning")
        );
        this.facture.montant = 0;
      }
    },

    //---------- keyup Received Amount

    Verified_Received_Amount() {
      if (isNaN(this.facture.received_amount)) {
        this.facture.received_amount = 0;
      } 
    },


    //---Validate State Fields
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },
    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------ Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_Supplier = "";
      this.Filter_status = "";
      this.Filter_Payment = "";
      this.Filter_Ref = "";
      this.Filter_date = "";
      (this.Filter_warehouse = ""),
        this.Get_purchase_returns(this.serverParams.page);
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

    //-----------------------------  Return purchase pdf------------------------------\\
    Return_PDF(purchase_return, id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
     
      axios
        .get("Return_Purchase_PDF/" + id, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute(
            "download",
            "purchase_return_" + purchase_return.Ref + ".pdf"
          );
          document.body.appendChild(link);
          link.click();
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

    //------------------------ Payment Return Purchase Pdf ------------------------------\\
    Payment_Return_PDF(facture, id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
     
       axios
        .get("payment_Return_Purchase_PDF/" + id, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "Payment-" + facture.Ref + ".pdf");
          document.body.appendChild(link);
          link.click();
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

    //----------------------------------------- Returns Purchase PDF -----------------------\\
    Returns_Purchase_PDF() {
      var self = this;

      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Ref", dataKey: "Ref" },
        { title: "Provider", dataKey: "provider_name" },
        { title: "Status", dataKey: "statut" },
        { title: "Total", dataKey: "GrandTotal" },
        { title: "Paid", dataKey: "paid_amount" },
        { title: "Due", dataKey: "due" },
        { title: "Status Payment", dataKey: "payment_status" }
      ];
      pdf.autoTable(columns, self.purchase_returns);
      pdf.text("Purchase Returns", 40, 25);
      pdf.save("purchase_returns.pdf");
    },

    //-------------------------------------- Returns Purchase Excel -----------------------\\
    Returns_Purchase_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("returns/purchase/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "Returns_purchase.xlsx");
          document.body.appendChild(link);
          link.click();
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

    Number_Order_Payment() {
      axios
        .get("payment/returns_purchase/Number/Order")
        .then(({ data }) => (this.facture.Ref = data));
    },

    //----------------------------------- Add Payment Return Purchase ------------------------------\\
    New_Payment(purchase_return) {
      if (purchase_return.payment_status == "paid") {
        this.$swal({
          icon: "error",
          title: "Oops...",
          text: this.$t("PaymentComplete")
        });
      } else {
        // Start the progress bar.
        NProgress.start();
        NProgress.set(0.1);
        this.reset_form_payment();
        this.EditPaiementMode = false;
        this.purchase_return = purchase_return;
        this.facture.date = new Date().toISOString().slice(0, 10);
        this.Number_Order_Payment();
        this.facture.montant = purchase_return.due;
        this.facture.Reglement = 'Cash';
        this.facture.received_amount = purchase_return.due;
        this.due = parseFloat(this.purchase_return.due);
        setTimeout(() => {
          // Complete the animation of the  progress bar.
          NProgress.done();
          this.$bvModal.show("Add_Payment");
        }, 500);
      }
    },

    //---- reset form payment

    reset_form_payment() {
      this.due = 0;
      this.facture = {
        id: "",
        purchase_return_id: "",
        date: "",
        Ref: "",
        montant: "",
        received_amount: "",
        Reglement: "",
        notes: ""
      };
    },

    //------------------------------------Edit Payment ------------------------------\\
    Edit_Payment(facture) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.reset_form_payment();
      this.EditPaiementMode = true;

      this.facture.id        = facture.id;
      this.facture.Ref       = facture.Ref;
      this.facture.Reglement = facture.Reglement;
      this.facture.date    = facture.date;
      this.facture.change  = facture.change;
      this.facture.montant = facture.montant;
      this.facture.received_amount = parseFloat(facture.montant + facture.change).toFixed(2);
      this.facture.notes   = facture.notes;

      this.due = parseFloat(this.purchase_return_due) + facture.montant;
      setTimeout(() => {
        // Complete the animation of the  progress bar.
        NProgress.done();
        this.$bvModal.show("Add_Payment");
      }, 1000);
    },

    //-------------------------------Show All Payment with Return Purchase ---------------------\\
    Show_Payments(id, purchase_return) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.reset_form_payment();
      this.purchase_return_id = id;
      this.purchase_return = purchase_return;
      this.Get_Payments(id);
    },

    //----------------------------------------- Get Payments Returns Purchase -------------------------------\\
    Get_Payments(id) {
      axios
        .get("returns/purchase/payment/" + id)
        .then(response => {
          this.factures = response.data.payments;
          this.purchase_return_due = response.data.due;
          setTimeout(() => {
            // Complete the animation of the  progress bar.
            NProgress.done();
            this.$bvModal.show("Show_payment");
          }, 500);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

      //---------SMS notification
     Payment_PurchaseReturn_SMS(facture) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("payment/returns_purchase/send/sms", {
          id: facture.id,
        })
        .then(response => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast(
            "success",
            this.$t("Send_SMS"),
            this.$t("Success")
          );
        })
        .catch(error => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast("danger", this.$t("sms_config_invalid"), this.$t("Failed"));
        });
    },


    //--------------------------------------------- Send Payment Return Purchase on Email -------------------------------\\

    EmailPayment(facture, purchase_return) {
      this.emailPayment.id = facture.id;
      this.emailPayment.to = purchase_return.provider_email;
      this.emailPayment.Ref = facture.Ref;
      this.emailPayment.client_name = purchase_return.provider_name;
      this.Send_Email_Payment();
    },

    Send_Email_Payment() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("payment/returns_purchase/send/email", {
          id: this.emailPayment.id,
          to: this.emailPayment.to,
          client_name: this.emailPayment.client_name,
          Ref: this.emailPayment.Ref
        })
        .then(response => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);

          this.makeToast(
            "success",
            this.$t("Send.TitleEmail"),
            this.$t("Success")
          );
        })
        .catch(error => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast("danger", this.$t("SMTPIncorrect"), this.$t("Failed"));
        });
    },

    //--------------------- Send Purchase Return on Email ------------------------\\

    Return_Purchase_Email(purchase_return) {
      this.email.to = purchase_return.provider_email;
      this.email.Return_Ref = purchase_return.Ref;
      this.email.provider_name = purchase_return.provider_name;
      this.Send_Email(purchase_return.id);
    },

    Send_Email(id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("returns/purchase/send/email", {
          id: id,
          to: this.email.to,
          client_name: this.email.provider_name,
          Ref: this.email.Return_Ref
        })
        .then(response => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast(
            "success",
            this.$t("Send.TitleEmail"),
            this.$t("Success")
          );
        })
        .catch(error => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
          this.makeToast("danger", this.$t("SMTPIncorrect"), this.$t("Failed"));
        });
    },

    //---------------------------------------- Set To Strings-------------------------\\
    setToStrings() {
      // Simply replaces null values with strings=''
      if (this.Filter_Supplier === null) {
        this.Filter_Supplier = "";
      } else if (this.Filter_warehouse === null) {
        this.Filter_warehouse = "";
      } else if (this.Filter_status === null) {
        this.Filter_status = "";
      } else if (this.Filter_Payment === null) {
        this.Filter_Payment = "";
      }
    },

    //--------------------- Get All Returns Purchase ------------------------\\
    Get_purchase_returns(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.setToStrings();
      axios
        .get(
          "returns/purchase?page=" +
            page +
            "&Ref=" +
            this.Filter_Ref +
            "&date=" +
            this.Filter_date +
            "&provider_id=" +
            this.Filter_Supplier +
            "&statut=" +
            this.Filter_status +
            "&warehouse_id=" +
            this.Filter_warehouse +
            "&payment_statut=" +
            this.Filter_Payment +
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
          this.purchase_returns = response.data.purchase_returns;
          this.suppliers = response.data.suppliers;
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

    //---------------------  Remove Return ------------------------\\
    Remove_Return(id) {
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
            .delete("returns/purchase/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.ReturnDeleted"),
                "success"
              );
              Fire.$emit("Delete_purchase_return");
            })
            .catch(() => {
              // Complete the animation of the  progress bar.
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

    //---- Delete purchase return by selection

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
            .post("returns/purchase/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.ReturnDeleted"),
                "success"
              );

              Fire.$emit("Delete_purchase_return");
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

    //----------------------------------Create Payment Return ------------------------------\\
    Create_Payment() {
     this.paymentProcessing = true;
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("payment/returns_purchase", {
          purchase_return_id: this.purchase_return.id,
          date: this.facture.date,
          montant: parseFloat(this.facture.montant).toFixed(2),
          received_amount: parseFloat(this.facture.received_amount).toFixed(2),
          change: parseFloat(this.facture.received_amount - this.facture.montant).toFixed(2),
          Reglement: this.facture.Reglement,
          notes: this.facture.notes
        })
        .then(response => {
          this.paymentProcessing = false;
          Fire.$emit("Create_payment_purchase_return");

          this.makeToast(
            "success",
            this.$t("Create.TitlePayment"),
            this.$t("Success")
          );
        })
        .catch(error => {
           this.paymentProcessing = false;
            NProgress.done();
        });
    },

    //---------------------------------------- Update Payment Return ------------------------------\\
    Update_Payment() {
      this.paymentProcessing = true;
      NProgress.start();
      NProgress.set(0.1);
      axios
        .put("payment/returns_purchase/" + this.facture.id, {
          purchase_return_id: this.purchase_return.id,
          date: this.facture.date,
          montant: parseFloat(this.facture.montant).toFixed(2),
          received_amount: parseFloat(this.facture.received_amount).toFixed(2),
          change: parseFloat(this.facture.received_amount - this.facture.montant).toFixed(2),
          Reglement: this.facture.Reglement,
          notes: this.facture.notes
        })
        .then(response => {
          this.paymentProcessing = false;
          Fire.$emit("Update_payment_purchase_return");

          this.makeToast(
            "success",
            this.$t("Update.TitlePayment"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.paymentProcessing = false;
          NProgress.done();
        });
    },

    //----------------------------------------- Delete Payment Return ------------------------------\\
    Remove_Payment(id) {
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
            .delete("payment/returns_purchase/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.PaymentDeleted")
              );
              Fire.$emit("Delete_payment_purchase_return");
            })
            .catch(() => {
              // Complete the animation of the  progress bar.
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
  }, //End Methods

  created() {
    this.Get_purchase_returns(1);

    Fire.$on("Create_payment_purchase_return", () => {
      setTimeout(() => {
        this.Get_purchase_returns(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
        this.$bvModal.hide("Add_Payment");
      }, 500);
    });

    Fire.$on("Update_payment_purchase_return", () => {
      setTimeout(() => {
        this.Get_Payments(this.purchase_return_id);
        this.Get_purchase_returns(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
        this.$bvModal.hide("Add_Payment");
      }, 500);
    });

    Fire.$on("Delete_payment_purchase_return", () => {
      setTimeout(() => {
        this.Get_Payments(this.purchase_return_id);
        this.Get_purchase_returns(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
      }, 500);
    });

    Fire.$on("Delete_purchase_return", () => {
      setTimeout(() => {
        this.Get_purchase_returns(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
      }, 500);
    });
  }
};
</script>
