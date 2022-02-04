<template>
  <div class="main-content">
    <breadcumb :page="$t('SuppliersManagement')" :folder="$t('Suppliers')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="providers"
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
          <b-button @click="Providers_PDF()" size="sm" variant="outline-success m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
          <b-button @click="Providers_Excel()" size="sm" variant="outline-danger m-1">
            <i class="i-File-Excel"></i> EXCEL
          </b-button>
          <b-button
            @click="Show_import_providers()"
            size="sm"
            variant="info m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('Suppliers_import')"
          >
            <i class="i-Download"></i>
            {{ $t("Import_Suppliers") }}
          </b-button>
          <b-button
            @click="New_Provider()"
            size="sm"
            variant="btn btn-primary btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('Suppliers_add')"
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
              @click="Edit_Provider(props.row)"
              v-if="currentUserPermissions && currentUserPermissions.includes('Suppliers_edit')"
              title="Edit"
              v-b-tooltip.hover
            >
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a
              title="Delete"
              v-b-tooltip.hover
              v-if="currentUserPermissions && currentUserPermissions.includes('Suppliers_delete')"
              @click="Remove_Provider(props.row.id)"
            >
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </div>

    <!-- Multiple Filters  -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- Code Provider   -->
          <b-col md="12">
            <b-form-group :label="$t('SupplierCode')">
              <b-form-input label="Code" :placeholder="$t('SearchByCode')" v-model="Filter_Code"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Name Provider   -->
          <b-col md="12">
            <b-form-group :label="$t('SupplierName')">
              <b-form-input label="Name" :placeholder="$t('SearchByName')" v-model="Filter_Name"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Phone Provider   -->
          <b-col md="12">
            <b-form-group :label="$t('Phone')">
              <b-form-input label="Phone" :placeholder="$t('SearchByPhone')" v-model="Filter_Phone"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Email Provider   -->
          <b-col md="12">
            <b-form-group :label="$t('Email')">
              <b-form-input label="Email" :placeholder="$t('SearchByEmail')" v-model="Filter_Email"></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button
              @click="Get_Providers(serverParams.page)"
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

    <!-- Add & Edit Provider -->
    <validation-observer ref="Create_Provider">
      <b-modal hide-footer size="lg" id="New_Provider" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Provider">
          <b-row>
            <!-- Provider Name -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Name Provider"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('SupplierName')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="name-feedback"
                    label="name"
                    v-model="provider.name"
                  ></b-form-input>
                  <b-form-invalid-feedback id="name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Provider Email -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Email Provider"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Email')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Email-feedback"
                    label="Email"
                    v-model="provider.email"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Email-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Provider Phone -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Phone Provider"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Phone')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Phone-feedback"
                    label="Phone"
                    v-model="provider.phone"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Phone-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Provider Country -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Country Provider"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Country')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Country-feedback"
                    label="Country"
                    v-model="provider.country"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Country-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Provider City -->
            <b-col md="6" sm="12">
              <validation-provider
                name="City Provider"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('City')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="City-feedback"
                    label="City"
                    v-model="provider.city"
                  ></b-form-input>
                  <b-form-invalid-feedback id="City-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Provider Adress -->
            <b-col md="6" sm="12">
              <validation-provider
                name="Adress Provider"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Adress')">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="Adress-feedback"
                    label="Adress"
                    v-model="provider.adresse"
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

    <!-- Show details Provider -->
    <b-modal ok-only size="md" id="showDetails" :title="$t('SupplierDetails')">
      <b-row>
        <b-col lg="12" md="12" sm="12" class="mt-3">
          <table class="table table-striped table-md">
            <tbody>
              <tr>
                <!-- Provider Code -->
                <td>{{$t('SupplierCode')}}</td>
                <th>{{provider.code}}</th>
              </tr>
              <tr>
                <!-- Provider Name -->
                <td>{{$t('SupplierName')}}</td>
                <th>{{provider.name}}</th>
              </tr>
              <tr>
                <!-- Provider Phone -->
                <td>{{$t('Phone')}}</td>
                <th>{{provider.phone}}</th>
              </tr>
              <tr>
                <!-- Provider Email -->
                <td>{{$t('Email')}}</td>
                <th>{{provider.email}}</th>
              </tr>
              <tr>
                <!-- Provider country -->
                <td>{{$t('Country')}}</td>
                <th>{{provider.country}}</th>
              </tr>
              <tr>
                <!-- Provider City -->
                <td>{{$t('City')}}</td>
                <th>{{provider.city}}</th>
              </tr>
              <tr>
                <!-- Provider Adress -->
                <td>{{$t('Adress')}}</td>
                <th>{{provider.adresse.substring(0, 24)}}</th>
              </tr>
            </tbody>
          </table>
        </b-col>
      </b-row>
    </b-modal>

    <!-- Modal Show Import Providers -->
    <b-modal
      ok-only
      ok-title="Cancel"
      size="md"
      id="importProviders"
      :title="$t('Import_Suppliers')"
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
              :href="'/import/exemples/import_providers.csv'"
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
                    <span class="badge badge-outline-success">{{$t('Field_is_required')}}</span>
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
    title: "Provider"
  },
  data() {
    return {
      editmode: false,
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
      selectedIds: [],
      totalRows: "",
      search: "",
      limit: "10",
      Filter_Name: "",
      Filter_Code: "",
      Filter_Phone: "",
      Filter_Email: "",
      import_providers: "",
      data: new FormData(),
      providers: [],
      provider: {
        id: "",
        name: "",
        code: "",
        phone: "",
        email: "",
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
    //------------- Submit Validation Create & Edit Provider
    Submit_Provider() {
      this.$refs.Create_Provider.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Provider();
          } else {
            this.Update_provider();
          }
        }
      });
    },

    //----------------------------------- Show import providers -------------------------------\\
    Show_import_providers() {
      this.$bvModal.show("importProviders");
    },

    //------------------------------ Event Import providers -------------------------------\\
    onFileSelected(e) {
      this.import_providers = "";
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
        this.import_providers = file;
      }
    },

    //----------------------------------------Submit  import providers-----------------\\
    Submit_import() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      var self = this;
      self.ImportProcessing = true;
      self.data.append("providers", self.import_providers);
      axios
        .post("providers/import/csv", self.data)
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

    //------ update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Providers(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Providers(1);
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
      this.Get_Providers(this.serverParams.page);
    },

    //------ Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Providers(this.serverParams.page);
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
      this.Get_Providers(this.serverParams.page);
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------------ Providers PDF -----------------------\\
    Providers_PDF() {
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
      pdf.autoTable(columns, self.providers);
      pdf.text("Provider List", 40, 25);
      pdf.save("Provider_List.pdf");
    },

    //------------ Providers Excel -----------------------\\

    Providers_Excel() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("providers/export/Excel", {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "List_Suppliers.xlsx");
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

    //------------------------------ Show Modal (create Provider) -------------------------------\\
    New_Provider() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Provider");
    },

    //------------------------------ Show Modal (Edit Provider) -------------------------------\\
    Edit_Provider(provider) {
      this.Get_Providers(this.serverParams.page);
      this.reset_Form();
      this.provider = provider;
      this.editmode = true;
      this.$bvModal.show("New_Provider");
    },

    //----------------------------  Get all Providers  -----------------------\\
    Get_Providers(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "providers?page=" +
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
          this.providers = response.data.providers;
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

    //---------------------------- Create Provider  -----------------------\\
    Create_Provider() {
      this.SubmitProcessing = true;
      axios
        .post("providers", {
          name: this.provider.name,
          email: this.provider.email,
          phone: this.provider.phone,
          country: this.provider.country,
          city: this.provider.city,
          adresse: this.provider.adresse
        })
        .then(response => {
          Fire.$emit("Event_Provider");

          this.makeToast(
            "success",
            this.$t("Create.TitleSupplier"),
            this.$t("Success")
          );
          this.SubmitProcessing = false;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    //--------------------------- Update Provider -----------------------\\
    Update_provider() {
      this.SubmitProcessing = true;
      axios
        .put("providers/" + this.provider.id, {
          name: this.provider.name,
          email: this.provider.email,
          phone: this.provider.phone,
          country: this.provider.country,
          city: this.provider.city,
          adresse: this.provider.adresse
        })
        .then(response => {
          Fire.$emit("Event_Provider");

          this.makeToast(
            "success",
            this.$t("Update.TitleSupplier"),
            this.$t("Success")
          );
          this.SubmitProcessing = false;
        })
        .catch(error => {
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    //----------------------------------- Show Details Client -------------------------------\\
    showDetails(provider) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.provider = provider;
      Fire.$emit("Get_Details_Provider");
    },

    //--------------------------------- Reset Form -----------------------\\
    reset_Form() {
      this.provider = {
        id: "",
        name: "",
        phone: "",
        email: "",
        country: "",
        city: "",
        adresse: ""
      };
    },

    //---------------------------- DELETE Provider -----------------------\\

    Remove_Provider(id) {
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
            .delete("providers/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.SupplierDeleted"),
                "success"
              );

              Fire.$emit("Delete_Provider");
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.ProviderError"),
                "warning"
              );
            });
        }
      });
    },

    //---- Delete providers by selection

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
            .post("providers/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.SupplierDeleted"),
                "success"
              );

              Fire.$emit("Delete_Provider");
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

  created: function() {
    this.Get_Providers(1);

    Fire.$on("Get_Details_Provider", () => {
      // Complete the animation of theprogress bar.
      setTimeout(() => NProgress.done(), 500);
      this.$bvModal.show("showDetails");
    });

    Fire.$on("Event_Provider", () => {
      setTimeout(() => {
        this.Get_Providers(this.serverParams.page);
        this.$bvModal.hide("New_Provider");
      }, 500);
    });

    Fire.$on("Delete_Provider", () => {
      setTimeout(() => {
        this.Get_Providers(this.serverParams.page);
      }, 500);
    });

    Fire.$on("Event_import", () => {
      setTimeout(() => {
        this.Get_Providers(this.serverParams.page);
        this.$bvModal.hide("importProviders");
      }, 500);
    });
  }
};
</script>
