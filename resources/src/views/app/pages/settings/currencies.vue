<template>
  <div class="main-content">
    <breadcumb :page="$t('Currencies')" :folder="$t('Settings')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :search-options="{
            enabled: true,
            placeholder:'search table'
          }"
        :totalRows="totalRows"
        :rows="currencies"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
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
          <b-button
            @click="New_Currency()"
            class="btn-rounded"
            variant="btn btn-primary btn-icon m-1"
          >
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Edit_Currency(props.row)" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a title="Delete" v-b-tooltip.hover @click="Remove_Currency(props.row.id)">
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </b-card>
    <validation-observer ref="Create_Currency">
      <b-modal hide-footer size="md" id="New_Currency" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Currency">
          <b-row>
            <!-- Code Currency -->
            <b-col md="12">
              <validation-provider
                name="Code Currency"
                :rules="{ required: true , min:2 , max:5}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('CurrencyCode')">
                  <b-form-input
                    :placeholder="$t('Enter_Code_Currency')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Code-feedback"
                    label="Code"
                    v-model="currency.code"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Code-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Name Currency -->
            <b-col md="12">
              <validation-provider
                name="Name Currency"
                :rules="{ required: true , min:3}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('CurrencyName')">
                  <b-form-input
                    :placeholder="$t('Enter_name_Currency')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Name-feedback"
                    label="Name"
                    v-model="currency.name"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Symbole Currency -->
            <b-col md="12">
              <validation-provider
                name="Symbole Currency"
                :rules="{ required: true , max:5}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Symbol')">
                  <b-form-input
                    :placeholder="$t('Enter_Symbol_Currency')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Symbole-feedback"
                    label="Symbole"
                    v-model="currency.symbol"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Symbole-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
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
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Currency"
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing:false,
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
      currencies: [],
      editmode: false,
      currency: {
        id: "",
        name: "",
        code: "",
        symbol: ""
      }
    };
  },

  computed: {
    columns() {
      return [
        {
          label: this.$t("CurrencyCode"),
          field: "code",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("CurrencyName"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Symbol"),
          field: "symbol",
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
        this.Get_Currency(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Currency(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //---- Event on SortChange
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Currency(this.serverParams.page);
    },

    //---- Event on Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Currency(this.serverParams.page);
    },

    //---- Validation State Form
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation Create & Edit Currency
    Submit_Currency() {
      this.$refs.Create_Currency.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Currency();
          } else {
            this.Update_Currency();
          }
        }
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

    //------------------------------ Modal (create Currency) -------------------------------\\
    New_Currency() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Currency");
    },

    //------------------------------ Modal (Update Currency) -------------------------------\\
    Edit_Currency(currency) {
      this.Get_Currency(this.serverParams.page);
      this.reset_Form();
      this.currency = currency;
      this.editmode = true;
      this.$bvModal.show("New_Currency");
    },

    //--------------------------Get ALL currencies ---------------------------\\

    Get_Currency(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "currencies?page=" +
            page +
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
          this.currencies = response.data.currencies;
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

    //----------------------------------Create new currency ----------------\\
    Create_Currency() {
      this.SubmitProcessing = true;
      axios
        .post("currencies", {
          name: this.currency.name,
          code: this.currency.code,
          symbol: this.currency.symbol
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Currency");

          this.makeToast(
            "success",
            this.$t("Create.TitleCurrency"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------------------- Update Currency ----------------\\
    Update_Currency() {
      this.SubmitProcessing = true;
      axios
        .put("currencies/" + this.currency.id, {
          name: this.currency.name,
          code: this.currency.code,
          symbol: this.currency.symbol
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Currency");

          this.makeToast(
            "success",
            this.$t("Update.TitleCurrency"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //--------------------------- reset Form ----------------\\

    reset_Form() {
      this.currency = {
        id: "",
        name: "",
        code: "",
        symbol: ""
      };
    },

    //--------------------------- Remove Currency----------------\\
    Remove_Currency(id) {
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
            .delete("currencies/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.TitleCurrency"),
                "success"
              );

              Fire.$emit("Delete_Currency");
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

    //---- Delete currency by selection

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
            .post("currencies/delete/by_selection", {
              selectedIds: this.selectedIds
            })
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.TitleCurrency"),
                "success"
              );

              Fire.$emit("Delete_Currency");
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

  //----------------------------- Created function-------------------

  created: function() {
    this.Get_Currency(1);

    Fire.$on("Event_Currency", () => {
      setTimeout(() => {
        this.Get_Currency(this.serverParams.page);
        this.$bvModal.hide("New_Currency");
      }, 500);
    });

    Fire.$on("Delete_Currency", () => {
      setTimeout(() => {
        this.Get_Currency(this.serverParams.page);
      }, 500);
    });
  }
};
</script>