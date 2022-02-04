<template>
  <div class="main-content">
    <breadcumb :page="$t('ListSales')" :folder="$t('Sales')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="sales"
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
          <b-button @click="Sales_PDF()" size="sm" variant="outline-success ripple m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="Sales_Excel()" size="sm" variant="outline-danger ripple m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
          <router-link
            class="btn-sm btn btn-primary ripple btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('Sales_add')"
            to="/app/sales/store"
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
                id="dropdown-right"
                variant="link"
                text="right align"
                toggle-class="text-decoration-none"
                size="lg"
                right
                no-caret
              >
                <template v-slot:button-content class="_r_btn border-0">
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                  <span class="_dot _r_block-dot bg-dark"></span>
                </template>
                <b-navbar-nav>
                  <b-dropdown-item title="Show" :to="'/app/sales/detail/'+props.row.id">
                    <i class="nav-icon i-Eye font-weight-bold mr-2"></i>
                    {{$t('SaleDetail')}}
                  </b-dropdown-item>
                </b-navbar-nav>

                <b-dropdown-item
                  title="Edit"
                  v-if="currentUserPermissions.includes('Sales_edit')"
                  :to="'/app/sales/edit/'+props.row.id"
                >
                  <i class="nav-icon i-Pen-2 font-weight-bold mr-2"></i>
                  {{$t('EditSale')}}
                </b-dropdown-item>

                <b-dropdown-item
                  v-if="currentUserPermissions.includes('payment_sales_view')"
                  @click="Show_Payments(props.row.id , props.row)"
                >
                  <i class="nav-icon i-Money-Bag font-weight-bold mr-2"></i>
                  {{$t('ShowPayment')}}
                </b-dropdown-item>

                <b-dropdown-item
                  v-if="currentUserPermissions.includes('payment_sales_add')"
                  @click="New_Payment(props.row)"
                >
                  <i class="nav-icon i-Add font-weight-bold mr-2"></i>
                  {{$t('AddPayment')}}
                </b-dropdown-item>

                <b-dropdown-item title="Invoice" @click="Invoice_POS(props.row.id)">
                  <i class="nav-icon i-File-TXT font-weight-bold mr-2"></i>
                  {{$t('Invoice_POS')}}
                </b-dropdown-item>

                <b-dropdown-item title="PDF" @click="Invoice_PDF(props.row , props.row.id)">
                  <i class="nav-icon i-File-TXT font-weight-bold mr-2"></i>
                  {{$t('DownloadPdf')}}
                </b-dropdown-item>

                <b-dropdown-item title="Email" @click="Sale_Email(props.row , props.row.id)">
                  <i class="nav-icon i-Envelope-2 font-weight-bold mr-2"></i>
                  {{$t('EmailSale')}}
                </b-dropdown-item>

                <b-dropdown-item
                  title="Delete"
                  v-if="currentUserPermissions.includes('Sales_delete')"
                  @click="Remove_Sale(props.row.id)"
                >
                  <i class="nav-icon i-Close-Window font-weight-bold mr-2"></i>
                  {{$t('DeleteSale')}}
                </b-dropdown-item>
              </b-dropdown>
            </div>
          </span>
          <div v-else-if="props.column.field == 'statut'">
            <span
              v-if="props.row.statut == 'completed'"
              class="badge badge-outline-success"
            >{{$t('complete')}}</span>
            <span
              v-else-if="props.row.statut == 'pending'"
              class="badge badge-outline-info"
            >{{$t('Pending')}}</span>
            <span v-else class="badge badge-outline-warning">{{$t('Ordered')}}</span>
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

          <!-- Customer  -->
          <b-col md="12">
            <b-form-group :label="$t('Customer')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Customer')"
                v-model="Filter_Client"
                :options="customers.map(customers => ({label: customers.name, value: customers.id}))"
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
                        {label: 'Ordered', value: 'ordered'},
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
              @click="Get_Sales(serverParams.page)"
              variant="primary btn-block ripple m-1"
              size="sm"
            >
              <i class="i-Filter-2"></i>
              {{ $t("Filter") }}
            </b-button>
          </b-col>
          <b-col md="6" sm="12">
            <b-button @click="Reset_Filter()" variant="danger ripple btn-block m-1" size="sm">
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
                <tr v-if="payments.length <= 0">
                  <td colspan="5">{{$t('NodataAvailable')}}</td>
                </tr>
                <tr v-for="payment in payments">
                  <td>{{payment.date}}</td>
                  <td>{{payment.Ref}}</td>
                  <td>{{currentUser.currency}} {{formatNumber(payment.montant,2)}}</td>
                  <td>{{payment.Reglement}}</td>
                  <td>
                    <div role="group" aria-label="Basic example" class="btn-group">
                      <span
                        title="Print"
                        class="btn btn-icon btn-info btn-sm"
                        @click="Payment_Sale_PDF(payment,payment.id)"
                      >
                        <i class="i-Billing"></i>
                      </span>
                      <span
                        v-if="currentUserPermissions.includes('payment_sales_edit')"
                        title="Edit"
                        class="btn btn-icon btn-success btn-sm"
                        @click="Edit_Payment(payment)"
                      >
                        <i class="i-Pen-2"></i>
                      </span>
                      <span
                        title="Email"
                        class="btn btn-icon btn-primary btn-sm"
                        @click="EmailPayment(payment , sale)"
                      >
                        <i class="i-Envelope"></i>
                      </span>
                      <span
                        title="SMS"
                        class="btn btn-icon btn-secondary btn-sm"
                        @click="Payment_Sale_SMS(payment)"
                      >
                        <i class="i-Speach-Bubble"></i>
                      </span>
                      <span
                        v-if="currentUserPermissions.includes('payment_sales_delete')"
                        title="Delete"
                        class="btn btn-icon btn-danger btn-sm"
                        @click="Remove_Payment(payment.id)"
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
                    v-model="payment.date"
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
                  v-model="payment.Ref"
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
                    @keyup="Verified_Received_Amount(payment.received_amount)"
                    label="Received_Amount"
                    :placeholder="$t('Received_Amount')"
                    v-model.number="payment.received_amount"
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
                   @keyup="Verified_paidAmount(payment.montant)"
                    label="Amount"
                    :placeholder="$t('Paying_Amount')"
                    v-model.number="payment.montant"
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
              >{{parseFloat(payment.received_amount - payment.montant).toFixed(2)}}</p>
            </b-col>

            <!-- Payment choice -->
            <b-col lg="6" md="12" sm="12">
              <validation-provider name="Payment choice" :rules="{ required: true}">
                <b-form-group slot-scope="{ valid, errors }" :label="$t('Paymentchoice')">
                  <v-select
                    :class="{'is-invalid': !!errors.length}"
                    :state="errors[0] ? false : (valid ? true : null)"
                    v-model="payment.Reglement"
                    @input="Selected_PaymentMethod"
                    :disabled="EditPaiementMode && payment.Reglement == 'credit card'"
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

            <b-col md="12" v-if="payment.Reglement == 'credit card'">
              <form id="payment-form">
                <label
                  for="card-element"
                  class="leading-7 text-sm text-gray-600"
                >{{$t('Credit_Card_Info')}}</label>
                <div id="card-element">
                  <!-- Elements will create input elements here -->
                </div>
                <!-- We'll put the error messages in this element -->
                <div id="card-errors" class="is-invalid" role="alert"></div>
              </form>
            </b-col>

            <!-- Note -->
            <b-col lg="12" md="12" sm="12" class="mt-3">
              <b-form-group :label="$t('Note')">
                <b-form-textarea id="textarea" v-model="payment.notes" rows="3" max-rows="6"></b-form-textarea>
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

    <!-- Modal Show Invoice POS-->
    <b-modal hide-footer size="sm" scrollable id="Show_invoice" :title="$t('Invoice_POS')">
        <div id="invoice-POS">
          <div style="max-width:400px;margin:0px auto">
          <div class="info" >
            <h2 class="text-center">{{invoice_pos.setting.CompanyName}}</h2>

            <p>
                <span>{{$t('date')}} : {{invoice_pos.sale.date}} <br></span>
                <span v-show="pos_settings.show_address">{{$t('Adress')}} : {{invoice_pos.setting.CompanyAdress}} <br></span>
                <span v-show="pos_settings.show_email">{{$t('Email')}} : {{invoice_pos.setting.email}} <br></span>
                <span v-show="pos_settings.show_phone">{{$t('Phone')}} : {{invoice_pos.setting.CompanyPhone}} <br></span>
                <span v-show="pos_settings.show_customer">{{$t('Customer')}} : {{invoice_pos.sale.client_name}} <br></span>
              </p>
          </div>

          <table>
            <tbody>
              <tr v-for="detail_invoice in invoice_pos.details">
                <td colspan="3"><span>{{detail_invoice.name}} <br> {{formatNumber(detail_invoice.quantity,2)}} {{detail_invoice.unit_sale}} x {{formatNumber(detail_invoice.total/detail_invoice.quantity,2)}}</span></td>
                <td style="text-align:right;vertical-align:bottom">{{formatNumber(detail_invoice.total,2)}}</td>
              </tr>


              <tr style="margin-top:10px">
                <td colspan="3" class="total">{{$t('OrderTax')}}</td>
                <td style="text-align:right;" class="total">{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.taxe ,2)}} ({{formatNumber(invoice_pos.sale.tax_rate,2)}} %)</td>
              </tr>

              <tr style="margin-top:10px">
                <td colspan="3" class="total">{{$t('Discount')}}</td>
                <td style="text-align:right;" class="total">{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.discount ,2)}}</td>
              </tr>

              <tr style="margin-top:10px">
                <td colspan="3" class="total">{{$t('Total')}}</td>
                <td style="text-align:right;" class="total">{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.GrandTotal ,2)}}</td>
              </tr>

                  <tr v-show="invoice_pos.sale.paid_amount < invoice_pos.sale.GrandTotal">
                    <td colspan="3" class="total">{{$t('Paid')}}</td>
                    <td
                      style="text-align:right;"
                      class="total"
                    >{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.paid_amount ,2)}}</td>
                  </tr>

                  <tr v-show="invoice_pos.sale.paid_amount < invoice_pos.sale.GrandTotal">
                    <td colspan="3" class="total">{{$t('Due')}}</td>
                    <td
                      style="text-align:right;"
                      class="total"
                    >{{invoice_pos.symbol}} {{parseFloat(invoice_pos.sale.GrandTotal - invoice_pos.sale.paid_amount).toFixed(2)}}</td>
                  </tr>
            </tbody>
          </table>

           <table
                class="change mt-3"
                style=" font-size: 10px;"
                v-show="invoice_pos.sale.paid_amount > 0"
              >
                <thead>
                  <tr style="background: #eee; ">
                    <th style="text-align: left;" colspan="1">{{$t('PayeBy')}}:</th>
                    <th style="text-align: center;" colspan="2">{{$t('Amount')}}:</th>
                    <th style="text-align: right;" colspan="1">{{$t('Change')}}:</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="payment_pos in payments">
                    <td style="text-align: left;" colspan="1">{{payment_pos.Reglement}}</td>
                    <td
                      style="text-align: center;"
                      colspan="2"
                    >{{formatNumber(payment_pos.montant ,2)}}</td>
                    <td
                      style="text-align: right;"
                      colspan="1"
                    >{{formatNumber(payment_pos.change ,2)}}</td>
                  </tr>
                </tbody>
              </table>

          <div id="legalcopy" class="ml-2">
            <p class="legal" v-show="pos_settings.show_note">
               <strong>{{pos_settings.note_customer}}</strong>
            </p>
            <div id="bar" v-show="pos_settings.show_barcode">
              <barcode
                class="barcode"
                :format="barcodeFormat"
                :value="invoice_pos.sale.Ref"
                textmargin="0"
                fontoptions="bold"
                fontSize= "15"
                height= "25"
                width= "1"
              ></barcode>
            </div>
            </div>
          </div>
        </div>
      <button @click="print_it()" class="btn btn-outline-primary">
        <i class="i-Billing"></i>
        {{$t('print')}}
      </button>
    </b-modal>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";
import vueEasyPrint from "vue-easy-print";
import VueBarcode from "vue-barcode";
import { loadStripe } from "@stripe/stripe-js";
export default {
  components: {
    vueEasyPrint,
    barcode: VueBarcode
  },
  metaInfo: {
    title: "Sales"
  },
  data() {
    return {
      stripe_key:'',
      stripe: {},
      cardElement: {},
      pos_settings:{},
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
      barcodeFormat: "CODE128",
      showDropdown: false,
      EditPaiementMode: false,
      Filter_Client: "",
      Filter_Ref: "",
      Filter_date: "",
      Filter_status: "",
      Filter_Payment: "",
      Filter_warehouse: "",
      customers: [],
      warehouses: [],
      sales: [],
      sale_due:'',
      due:0,
      invoice_pos: {
        sale: {
          Ref: "",
          client_name: "",
          discount: "",
          taxe: "",
          tax_rate: "",
          shipping: "",
          GrandTotal: "",
          paid_amount:'',
        },
        details: [],
        setting: {
          logo: "",
          CompanyName: "",
          CompanyAdress: "",
          email: "",
          CompanyPhone: ""
        }
      },
      payments: [],
      payment: {},
      Sale_id: "",
      limit: "10",
      sale: {},
      email: {
        to: "",
        subject: "",
        message: "",
        client_name: "",
        Sale_Ref: ""
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
          label: this.$t("Customer"),
          field: "client_name",
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

     async loadStripe_payment() {
      this.stripe = await loadStripe(`${this.stripe_key}`);
      const elements = this.stripe.elements();

      this.cardElement = elements.create("card", {
        classes: {
          base:
            "bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 p-3 leading-8 transition-colors duration-200 ease-in-out"
        }
      });

      this.cardElement.mount("#card-element");
    },


    //---------------------- Event Select Payment Method ------------------------------\\
    Selected_PaymentMethod(value) {
      if (value == "credit card") {
        setTimeout(() => {
          this.loadStripe_payment();
        }, 500);
      }
    },

      //------------------------------ Print -------------------------\\
    print_it() {
      var divContents = document.getElementById("invoice-POS").innerHTML;
      var a = window.open("", "", "height=500, width=500");
      a.document.write(
        '<link rel="stylesheet" href="/css/pos_print.css"><html>'
      );
      a.document.write("<body >");
      a.document.write(divContents);
      a.document.write("</body></html>");
      a.document.close();
      a.print();
    },
    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },
    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Sales(currentPage);
      }
    },
    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Sales(1);
      }
    },
    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },
    //---- Event Sort change
    onSortChange(params) {
      let field = "";
      if (params[0].field == "client_name") {
        field = "client_id";
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
      this.Get_Sales(this.serverParams.page);
    },
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Sales(this.serverParams.page);
    },

     //---------- keyup paid Amount

    Verified_paidAmount() {
      if (isNaN(this.payment.montant)) {
        this.payment.montant = 0;
      } else if (this.payment.montant > this.payment.received_amount) {
        this.makeToast(
          "warning",
          this.$t("Paying_amount_is_greater_than_Received_amount"),
          this.$t("Warning")
        );
        this.payment.montant = 0;
      } 
      else if (this.payment.montant > this.due) {
        this.makeToast(
          "warning",
          this.$t("Paying_amount_is_greater_than_Grand_Total"),
          this.$t("Warning")
        );
        this.payment.montant = 0;
      }
    },

    //---------- keyup Received Amount

    Verified_Received_Amount() {
      if (isNaN(this.payment.received_amount)) {
        this.payment.received_amount = 0;
      } 
    },


    //------ Validate Form Submit_Payment
    Submit_Payment() {
      this.$refs.Add_payment.validate().then(success => {
        if (!success) {
          return;
        } else if (this.payment.montant > this.payment.received_amount) {
          this.makeToast(
            "warning",
            this.$t("Paying_amount_is_greater_than_Received_amount"),
            this.$t("Warning")
          );
          this.payment.received_amount = 0;
        }
        else if (this.payment.montant > this.due) {
          this.makeToast(
            "warning",
            this.$t("Paying_amount_is_greater_than_Grand_Total"),
            this.$t("Warning")
          );
          this.payment.montant = 0;

        }else if (!this.EditPaiementMode) {
            this.Create_Payment();
        } else {
            this.Update_Payment();
        }

      });
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
      this.Filter_Client = "";
      this.Filter_status = "";
      this.Filter_Payment = "";
      this.Filter_Ref = "";
      this.Filter_date = "";
      (this.Filter_warehouse = ""), this.Get_Sales(this.serverParams.page);
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
    //----------------------------------- Sales PDF ------------------------------\\
    Sales_PDF() {
      var self = this;
      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Ref", dataKey: "Ref" },
        { title: "Client", dataKey: "client_name" },
        { title: "Status", dataKey: "statut" },
        { title: "Total", dataKey: "GrandTotal" },
        { title: "Paid", dataKey: "paid_amount" },
        { title: "Due", dataKey: "due" },
        { title: "Status Payment", dataKey: "payment_status" }
      ];
      pdf.autoTable(columns, self.sales);
      pdf.text("Sale List", 40, 25);
      pdf.save("Sale_List.pdf");
    },
    //-------------------------------- Invoice POS ------------------------------\\
    Invoice_POS(id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("Sales/Print_Invoice/" + id)
        .then(response => {
          this.invoice_pos = response.data;
          this.payments = response.data.payments;
          this.pos_settings = response.data.pos_settings;
          setTimeout(() => {
            // Complete the animation of the  progress bar.
            NProgress.done();
            this.$bvModal.show("Show_invoice");
          }, 500);
          setTimeout(() => this.print_it(), 1000);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },
    //-------------------------------- Sales Excel ------------------------------\\
    Sales_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("sales/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "List_Sales.xlsx");
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
    //-----------------------------  Invoice PDF ------------------------------\\
    Invoice_PDF(sale, id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
       axios
        .get("Sale_PDF/" + id, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "Sale-" + sale.Ref + ".pdf");
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
    //------------------------ Payments Sale PDF ------------------------------\\
    Payment_Sale_PDF(payment, id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
     
      axios
        .get("payment_Sale_PDF/" + id, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "Payment-" + payment.Ref + ".pdf");
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
    //---------------------------------------- Set To Strings-------------------------\\
    setToStrings() {
      // Simply replaces null values with strings=''
      if (this.Filter_Client === null) {
        this.Filter_Client = "";
      } else if (this.Filter_warehouse === null) {
        this.Filter_warehouse = "";
      } else if (this.Filter_status === null) {
        this.Filter_status = "";
      } else if (this.Filter_Payment === null) {
        this.Filter_Payment = "";
      }
    },
    //----------------------------------------- Get all Sales ------------------------------\\
    Get_Sales(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.setToStrings();
      axios
        .get(
          "sales?page=" +
            page +
            "&Ref=" +
            this.Filter_Ref +
            "&date=" +
            this.Filter_date +
            "&client_id=" +
            this.Filter_Client +
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
          this.sales = response.data.sales;
          this.customers = response.data.customers;
          this.warehouses = response.data.warehouses;
          this.totalRows = response.data.totalRows;
          this.stripe_key = response.data.stripe_key;
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

    //---------SMS notification
     Payment_Sale_SMS(payment) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("payment/sale/send/sms", {
          id: payment.id,
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


    //--------------------------------------------- Send Payment to Email -------------------------------\\
    EmailPayment(payment, sale) {
      this.emailPayment.id = payment.id;
      this.emailPayment.to = sale.client_email;
      this.emailPayment.Ref = payment.Ref;
      this.emailPayment.client_name = sale.client_name;
      this.Send_Email_Payment();
    },
    Send_Email_Payment() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("payment/sale/send/email", {
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
    //--------------------------------- Send Sale in Email ------------------------------\\
    Sale_Email(sale) {
      this.email.to = sale.client_email;
      this.email.Sale_Ref = sale.Ref;
      this.email.client_name = sale.client_name;
      this.Send_Email(sale.id);
    },
    Send_Email(id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("sales/send/email", {
          id: id,
          to: this.email.to,
          client_name: this.email.client_name,
          Ref: this.email.Sale_Ref
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
    Number_Order_Payment() {
      axios
        .get("payment/sale/Number/Order")
        .then(({ data }) => (this.payment.Ref = data));
    },
    //----------------------------------- New Payment Sale ------------------------------\\
    New_Payment(sale) {
      if (sale.payment_status == "paid") {
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
        this.sale = sale;
        this.payment.date = new Date().toISOString().slice(0, 10);
        this.Number_Order_Payment();
        this.payment.montant = sale.due;
        this.payment.Reglement = 'Cash';
        this.payment.received_amount = sale.due;
        this.due = parseFloat(sale.due);
        setTimeout(() => {
          // Complete the animation of the  progress bar.
          NProgress.done();
          this.$bvModal.show("Add_Payment");
        }, 500);
      }
    },
    //------------------------------------Edit Payment ------------------------------\\
    Edit_Payment(payment) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.reset_form_payment();
      this.EditPaiementMode = true;

      this.payment.id        = payment.id;
      this.payment.Ref       = payment.Ref;
      this.payment.Reglement = payment.Reglement;
      this.payment.date    = payment.date;
      this.payment.change  = payment.change;
      this.payment.montant = payment.montant;
      this.payment.received_amount = parseFloat(payment.montant + payment.change).toFixed(2);
      this.payment.notes   = payment.notes;

      this.due = parseFloat(this.sale_due) + payment.montant;
      setTimeout(() => {
        // Complete the animation of the  progress bar.
        NProgress.done();
        this.$bvModal.show("Add_Payment");
      }, 1000);
      if (payment.Reglement == "credit card") {
        setTimeout(() => {
          this.loadStripe_payment();
        }, 500);
      }
    },
    //-------------------------------Show All Payment with Sale ---------------------\\
    Show_Payments(id, sale) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.reset_form_payment();
      this.Sale_id = id;
      this.sale = sale;
      this.Get_Payments(id);
    },
    //----------------------------------Process Payment (Mode Create) ------------------------------\\
    async processPayment_Create() {
      const { token, error } = await this.stripe.createToken(
        this.cardElement
      );
      if (error) {
        this.paymentProcessing = false;
        NProgress.done();
        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
      } else {
        axios
          .post("payment/sale", {
            sale_id: this.sale.id,
            client_email: this.sale.client_email,
            client_id: this.sale.client_id,
            date: this.payment.date,
            montant: parseFloat(this.payment.montant).toFixed(2),
            received_amount: parseFloat(this.payment.received_amount).toFixed(2),
            change: parseFloat(this.payment.received_amount - this.payment.montant).toFixed(2),
            Reglement: this.payment.Reglement,
            notes: this.payment.notes,
            token: token.id
          })
          .then(response => {
            this.paymentProcessing = false;
            Fire.$emit("Create_Facture_sale");
            this.makeToast(
              "success",
              this.$t("Create.TitlePayment"),
              this.$t("Success")
            );
          })
          .catch(error => {
            this.paymentProcessing = false;
            // Complete the animation of the  progress bar.
            NProgress.done();
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          });
      }
    },
    //----------------------------------Process Payment (Mode Edit) ------------------------------\\
    async processPayment_Update() {
       const { token, error } = await this.stripe.createToken(
        this.cardElement
      );
      if (error) {
        this.paymentProcessing = false;
        NProgress.done();
        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
      } else {
        axios
          .put("payment/sale/" + this.payment.id, {
            sale_id: this.sale.id,
            client_email: this.sale.client_email,
            client_id: this.sale.client_id,
            date: this.payment.date,
            montant: parseFloat(this.payment.montant).toFixed(2),
            received_amount: parseFloat(this.payment.received_amount).toFixed(2),
            change: parseFloat(this.payment.received_amount - this.payment.montant).toFixed(2),
            Reglement: this.payment.Reglement,
            notes: this.payment.notes,
            token: token.id
          })
          .then(response => {
            this.paymentProcessing = false;
            Fire.$emit("Update_Facture_sale");
            this.makeToast(
              "success",
              this.$t("Update.TitlePayment"),
              this.$t("Success")
            );
          })
          .catch(error => {
            this.paymentProcessing = false;
            // Complete the animation of the  progress bar.
            NProgress.done();
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          });
      }
    },
    //----------------------------------Create Payment sale ------------------------------\\
    Create_Payment() {
      this.paymentProcessing = true;
      NProgress.start();
      NProgress.set(0.1);
       if(this.payment.Reglement  == 'credit card'){
          if(this.stripe_key != ''){
            this.processPayment_Create();
          }else{
            this.makeToast("danger", this.$t("credit_card_account_not_available"), this.$t("Failed"));
            NProgress.done();
            this.paymentProcessing = false;
          }
        }else{
        axios
          .post("payment/sale", {
            sale_id: this.sale.id,
            date: this.payment.date,
            montant: parseFloat(this.payment.montant).toFixed(2),
            received_amount: parseFloat(this.payment.received_amount).toFixed(2),
            change: parseFloat(this.payment.received_amount - this.payment.montant).toFixed(2),
            Reglement: this.payment.Reglement,
            notes: this.payment.notes
          })
          .then(response => {
            this.paymentProcessing = false;
            Fire.$emit("Create_Facture_sale");
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
      }
    },
    //---------------------------------------- Update Payment ------------------------------\\
    Update_Payment() {
      this.paymentProcessing = true;
      NProgress.start();
      NProgress.set(0.1);
       if(this.payment.Reglement  == 'credit card'){
          if(this.stripe_key != ''){
            this.processPayment_Update();
          }else{
            this.makeToast("danger", this.$t("credit_card_account_not_available"), this.$t("Failed"));
            NProgress.done();
            this.paymentProcessing = false;
          }
        }else{
        axios
          .put("payment/sale/" + this.payment.id, {
            sale_id: this.sale.id,
            date: this.payment.date,
            montant: parseFloat(this.payment.montant).toFixed(2),
            received_amount: parseFloat(this.payment.received_amount).toFixed(2),
            change: parseFloat(this.payment.received_amount - this.payment.montant).toFixed(2),
            Reglement: this.payment.Reglement,
            notes: this.payment.notes
          })
          .then(response => {
            this.paymentProcessing = false;
            Fire.$emit("Update_Facture_sale");
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
      }
    },
    //----------------------------------------- Remove Payment ------------------------------\\
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
            .delete("payment/sale/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.PaymentDeleted"),
                "success"
              );
              Fire.$emit("Delete_Facture_sale");
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
    //----------------------------------------- Get Payments  -------------------------------\\
    Get_Payments(id) {
      axios
        .get("sales/payments/" + id)
        .then(response => {
          this.payments = response.data.payments;
          this.sale_due = response.data.due;
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
    //------------------------------------------ Reset Form Payment ------------------------------\\
    reset_form_payment() {
      this.due = 0;
      this.payment = {
        id: "",
        Sale_id: "",
        date: "",
        Ref: "",
        montant: "",
        received_amount: "",
        Reglement: "",
        notes: ""
      };
    },
    //------------------------------------------ Remove Sale ------------------------------\\
    Remove_Sale(id) {
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
            .delete("sales/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.SaleDeleted"),
                "success"
              );
              Fire.$emit("Delete_sale");
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
    //---- Delete sales by selection
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
            .post("sales/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.SaleDeleted"),
                "success"
              );
              Fire.$emit("Delete_sale");
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
  //----------------------------- Created function-------------------\\
  created() {
    this.Get_Sales(1);
    Fire.$on("Create_Facture_sale", () => {
      setTimeout(() => {
        this.Get_Sales(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
        this.$bvModal.hide("Add_Payment");
      }, 500);
    });
    Fire.$on("Update_Facture_sale", () => {
      setTimeout(() => {
        this.Get_Payments(this.Sale_id);
        this.Get_Sales(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
        this.$bvModal.hide("Add_Payment");
      }, 500);
    });
    Fire.$on("Delete_Facture_sale", () => {
      setTimeout(() => {
        this.Get_Payments(this.Sale_id);
        this.Get_Sales(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
      }, 500);
    });
    Fire.$on("Delete_sale", () => {
      setTimeout(() => {
        this.Get_Sales(this.serverParams.page);
        // Complete the animation of the  progress bar.
        NProgress.done();
      }, 500);
    });
  }
};
</script>

<style>
  .total{
    font-weight: bold;
    font-size: 14px;
    /* text-transform: uppercase;
    height: 50px; */
  }
</style>
