<template>
  <div class="main-content">
    <breadcumb :page="$t('CustomerManagement')" :folder="$t('Customers')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="clients"
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
          <b-button @click="clients_PDF()" size="sm" variant="outline-success m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="clients_Excel()" size="sm" variant="outline-danger m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
          <b-button
            @click="Show_import_clients()"
            size="sm"
            variant="info m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('customers_import')"
          >
            <i class="i-Download"></i>
            {{ $t("Import_Customers") }}
          </b-button>
          <b-button
            @click="New_Client()"
            size="sm"
            variant="btn btn-primary btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('Customers_add')"
          >
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a title="View" v-b-tooltip.hover @click="showDetails(props.row)">
              <i class="i-Eye text-25 text-info"></i>
            </a>
            <a
              @click="Edit_Client(props.row)"
              v-if="currentUserPermissions && currentUserPermissions.includes('Customers_edit')"
              title="Edit"
              v-b-tooltip.hover
            >
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a
              title="Delete"
              v-b-tooltip.hover
              v-if="currentUserPermissions && currentUserPermissions.includes('Customers_delete')"
              @click="Remove_Client(props.row.id)"
            >
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </div>

    <!-- Multiple filters -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- Code Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('CustomerCode')">
              <b-form-input label="Code" :placeholder="$t('SearchByCode')" v-model="Filter_Code"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Name Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('CustomerName')">
              <b-form-input label="Name" :placeholder="$t('SearchByName')" v-model="Filter_Name"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Phone Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('Phone')">
              <b-form-input label="Phone" :placeholder="$t('SearchByPhone')" v-model="Filter_Phone"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Email Customer   -->
          <b-col md="12">
            <b-form-group :label="$t('Email')">
              <b-form-input label="Email" :placeholder="$t('SearchByEmail')" v-model="Filter_Email"></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button @click="Get_Clients(serverParams.page)" variant="primary m-1" size="sm" block>
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

    <!-- Modal Create & Edit Customer -->
    <validation-observer ref="Create_Customer">
      <b-modal hide-footer size="lg" id="New_Customer" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Customer">
          <b-row>
            <!-- Customer Name -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Name Customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('CustomerName')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="name-feedback"
                    label="name"
                    v-model="client.name"
                  ></b-form-input>
                  <b-form-invalid-feedback id="name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Customer Email -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Email customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Email')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Email-feedback"
                    label="Email"
                    v-model="client.email"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Email-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                  <b-alert
                    show
                    variant="danger"
                    class="error mt-1"
                    v-if="email_exist !=''"
                  >{{email_exist}}</b-alert>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Customer Phone -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Phone Customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Phone')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Phone-feedback"
                    label="Phone"
                    v-model="client.phone"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Phone-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Customer Country -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Country customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Country')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Country-feedback"
                    label="Country"
                    v-model="client.country"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Country-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Customer City -->
            <b-col md="6" sm="12">
              <validation-provider
                name="City Customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('City')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="City-feedback"
                    label="City"
                    v-model="client.city"
                  ></b-form-input>
                  <b-form-invalid-feedback id="City-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Customer Adress -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Adress customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Adress')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Adress-feedback"
                    label="Adress"
                    v-model="client.adresse"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Adress-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <b-col md="12" class="mt-3">
                <b-button variant="primary" type="submit"  :disabled="SubmitProcessing">{{$t('submit')}}</b-button>
                  <div v-once class="typo__p" v-if="SubmitProcessing">
                    <div class="spinner sm spinner-primary mt-3"></div>
                  </div>
            </b-col>

          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>

    <!-- Modal Show Customer Details -->
    <b-modal ok-only size="md" id="showDetails" :title="$t('CustomerDetails')">
      <b-row>
        <b-col lg="12" md="12" sm="12" class="mt-3">
          <table class="table table-striped table-md">
            <tbody>
              <tr>
                <!-- Customer Code -->
                <td>{{$t('CustomerCode')}}</td>
                <th>{{client.code}}</th>
              </tr>
              <tr>
                <!-- Customer Name -->
                <td>{{$t('CustomerName')}}</td>
                <th>{{client.name}}</th>
              </tr>
              <tr>
                <!-- Customer Phone -->
                <td>{{$t('Phone')}}</td>
                <th>{{client.phone}}</th>
              </tr>
              <tr>
                <!-- Customer Email -->
                <td>{{$t('Email')}}</td>
                <th>{{client.email}}</th>
              </tr>
              <tr>
                <!-- Customer country -->
                <td>{{$t('Country')}}</td>
                <th>{{client.country}}</th>
              </tr>
              <tr>
                <!-- Customer City -->
                <td>{{$t('City')}}</td>
                <th>{{client.city}}</th>
              </tr>
              <tr>
                <!-- Customer Adress -->
                <td>{{$t('Adress')}}</td>
                <th>{{client.adresse.substring(0, 24)}}</th>
              </tr>
            </tbody>
          </table>
        </b-col>
      </b-row>
    </b-modal>

    <!-- Modal Show Import Clients -->
    <b-modal ok-only ok-title="Cancel" size="md" id="importClients" :title="$t('Import_Customers')">
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
              :href="'/import/exemples/import_clients.csv'"
              variant="info"
              size="sm"
              block
            >{{ $t("Download_exemple") }}</b-button>
          </b-col>

          <b-col md="12" sm="12">
            <table class="table table-bordered table-sm mt-4">
              <tbody>
                <tr>
                  <td>{{$t('Name')}}</td>
                  <th>
                    <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                  </th>
                </tr>

                <tr>
                  <td>{{$t('Phone')}}</td>
                  <th>
                    <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                  </th>
                </tr>

                <tr>
                  <td>{{$t('Email')}}</td>
                  <th>
                    <span class="badge badge-outline-success">{{$t('Field_is_required')}} | unique</span>
                  </th>
                </tr>

                <tr>
                  <td>{{$t('Country')}}</td>
                  <th>
                    <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                  </th>
                </tr>

                <tr>
                  <td>{{$t('City')}}</td>
                  <th>
                    <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                  </th>
                </tr>

                <tr>
                  <td>{{$t('Adress')}}</td>
                  <th>
                    <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
                  </th>
                </tr>
              </tbody>
            </table>
          </b-col>
        </b-row>
      </b-form>
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
    title: "Customer"
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing:false,
      ImportProcessing:false,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      email_exist:"",
      selectedIds: [],
      totalRows: "",
      search: "",
      limit: "10",
      Filter_Name: "",
      Filter_Code: "",
      Filter_Phone: "",
      Filter_Email: "",
      clients: [],
      editmode: false,
      import_clients: "",
      data: new FormData(),
      client: {
        id: "",
        name: "",
        code: "",
        email: "",
        phone: "",
        country: "",
        city: "",
        adresse: ""
      }
    };
  },

  computed: {
    ...mapGetters(["currentUserPermissions"]),
    columns() {
      return [
        {
          label: this.$t("Code"),
          field: "code",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Name"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },

        {
          label: this.$t("Phone"),
          field: "phone",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Email"),
          field: "email",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Country"),
          field: "country",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("City"),
          field: "city",
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
    //------------- Submit Validation Create & Edit Customer
    Submit_Customer() {
      this.$refs.Create_Customer.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Client();
          } else {
            this.Update_Client();
          }
        }
      });
    },

    //------ update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Clients(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Clients(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //------ Event Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Clients(this.serverParams.page);
    },

    //------ Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Clients(this.serverParams.page);
    },

    //------ Event Validation State
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------ Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_Name = "";
      this.Filter_Code = "";
      this.Filter_Phone = "";
      this.Filter_Email = "";
      this.Get_Clients(this.serverParams.page);
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //--------------------------------- Customers PDF -------------------------------\\
    clients_PDF() {
      var self = this;

      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Code", dataKey: "code" },
        { title: "Name", dataKey: "name" },
        { title: "Phone", dataKey: "phone" },
        { title: "Email", dataKey: "email" },
        { title: "Country", dataKey: "country" },
        { title: "City", dataKey: "city" }
      ];
      pdf.autoTable(columns, self.clients);
      pdf.text("Customer List", 40, 25);
      pdf.save("Customer_List.pdf");
    },

    //--------------------------------- Clients Excel -------------------------------\\
    clients_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("clients/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "List_Customers.xlsx");
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

    //--------------------------------------- Get All Clients -------------------------------\\
    Get_Clients(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "clients?page=" +
            page +
            "&name=" +
            this.Filter_Name +
            "&code=" +
            this.Filter_Code +
            "&phone=" +
            this.Filter_Phone +
            "&email=" +
            this.Filter_Email +
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
          this.clients = response.data.clients;
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

    //----------------------------------- Show import Client -------------------------------\\
    Show_import_clients() {
      this.$bvModal.show("importClients");
    },

    //------------------------------ Event Import clients -------------------------------\\
    onFileSelected(e) {
      this.import_clients = "";
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
        this.import_clients = file;
      }
    },

    //----------------------------------------Submit  import clients-----------------\\
    Submit_import() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      var self = this;
      self.ImportProcessing = true;
      self.data.append("clients", self.import_clients);
      axios
        .post("clients/import/csv", self.data)
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
          NProgress.done();
            this.makeToast(
              "danger",
              this.$t("Please_follow_the_import_instructions"),
              this.$t("Failed")
            );
        });
    },

    //----------------------------------- Show Details Client -------------------------------\\
    showDetails(client) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.client = client;
      Fire.$emit("Get_Details_customers");
    },

    //------------------------------ Show Modal (create Client) -------------------------------\\
    New_Client() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Customer");
    },

    //------------------------------ Show Modal (Edit Client) -------------------------------\\
    Edit_Client(client) {
      this.Get_Clients(this.serverParams.page);
      this.reset_Form();
      this.client = client;
      this.editmode = true;
      this.$bvModal.show("New_Customer");
    },

    //---------------------------------------- Create new Client -------------------------------\\
    Create_Client() {
      this.SubmitProcessing = true;
      axios
        .post("clients", {
          name: this.client.name,
          email: this.client.email,
          phone: this.client.phone,
          country: this.client.country,
          city: this.client.city,
          adresse: this.client.adresse
        })
        .then(response => {
          Fire.$emit("Event_Customer");

          this.makeToast(
            "success",
            this.$t("Create.TitleCustomer"),
            this.$t("Success")
          );
          this.SubmitProcessing = false;
        })
        .catch(error => {
          if (error.errors.email.length > 0) {
            this.email_exist = error.errors.email[0];
          }
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    //----------------------------------- Update Client -------------------------------\\
    Update_Client() {
      this.SubmitProcessing = true;
      axios
        .put("clients/" + this.client.id, {
          name: this.client.name,
          email: this.client.email,
          phone: this.client.phone,
          country: this.client.country,
          city: this.client.city,
          adresse: this.client.adresse
        })
        .then(response => {
          Fire.$emit("Event_Customer");
          this.makeToast(
            "success",
            this.$t("Update.TitleCustomer"),
            this.$t("Success")
          );
          this.SubmitProcessing = false;
        })
        .catch(error => {
          if (error.errors.email.length > 0) {
            this.email_exist = error.errors.email[0];
          }
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    //-------------------------------- Reset Form -------------------------------\\
    reset_Form() {
      this.email_exist= "";
      this.client = {
        id: "",
        name: "",
        email: "",
        phone: "",
        country: "",
        city: "",
        adresse: ""
      };
    },

    //------------------------------- Remove Client -------------------------------\\
    Remove_Client(id) {
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
          axios
            .delete("clients/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.CustomerDeleted"),
                "success"
              );
              Fire.$emit("Delete_Customer");
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.ClientError"),
                "warning"
              );
            });
        }
      });
    },

    //---- Delete Clients by selection

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
            .post("clients/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.CustomerDeleted"),
                "success"
              );

              Fire.$emit("Delete_Customer");
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
  }, // END METHODS

  //----------------------------- Created function-------------------

  created: function() {
    this.Get_Clients(1);

    Fire.$on("Get_Details_customers", () => {
      // Complete the animation of theprogress bar.
      setTimeout(() => NProgress.done(), 500);
      this.$bvModal.show("showDetails");
    });

    Fire.$on("Event_Customer", () => {
      setTimeout(() => {
        this.Get_Clients(this.serverParams.page);
        this.$bvModal.hide("New_Customer");
      }, 500);
    });

    Fire.$on("Delete_Customer", () => {
      setTimeout(() => {
        this.Get_Clients(this.serverParams.page);
      }, 500);
    });

    Fire.$on("Event_import", () => {
      setTimeout(() => {
        this.Get_Clients(this.serverParams.page);
        this.$bvModal.hide("importClients");
      }, 500);
    });
  }
};
</script>
