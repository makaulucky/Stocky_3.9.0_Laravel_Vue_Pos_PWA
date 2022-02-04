<template>
  <div class="main-content">
    <breadcumb :page="$t('Units')" :folder="$t('Settings')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="units"
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
        :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button @click="New_Unit()" class="btn-rounded" variant="btn btn-primary btn-icon m-1">
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Edit_Unit(props.row)" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a title="Delete" v-b-tooltip.hover @click="Remove_Unit(props.row.id)">
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
          <div v-else-if="props.column.field == 'BaseUnit'">
            <span v-if="props.row.base_unit_name != ''">{{props.row.base_unit_name}}</span>
            <span v-else>N/D</span>
          </div>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_Unit">
      <b-modal hide-footer size="md" id="New_Unit" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Unit">
          <b-row>
            <!-- Name -->
            <b-col md="12">
              <validation-provider
                name="Code Currency"
                :rules="{ required: true , max:15}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Name')">
                  <b-form-input
                    :placeholder="$t('Enter_Name_Unit')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Name-feedback"
                    label="Name"
                    v-model="unit.name"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- ShortName -->
            <b-col md="12">
              <validation-provider
                name="ShortName"
                :rules="{ required: true , max:15}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('ShortName')">
                  <b-form-input
                    :placeholder="$t('Enter_ShortName_Unit')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="ShortName-feedback"
                    label="ShortName"
                    v-model="unit.ShortName"
                  ></b-form-input>
                  <b-form-invalid-feedback id="ShortName-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <!-- Base unit -->
            <b-col md="12">
              <b-form-group :label="$t('BaseUnit')">
                <v-select
                  @input="Selected_Base_Unit"
                  v-model="unit.base_unit"
                  :reduce="label => label.value"
                  :placeholder="$t('Choose_Base_Unit')"
                  :options="units_base.map(units_base => ({label: units_base.name, value: units_base.id}))"
                />
              </b-form-group>
            </b-col>
            <!-- operator  -->
            <b-col md="12" v-show="show_operator">
              <b-form-group :label="$t('Operator')">
                <v-select
                  v-model="unit.operator"
                  :reduce="label => label.value"
                  :placeholder="$t('Choose_Operator')"
                  :options="
                        [
                        {label: 'Multiply (*)', value: '*'},
                        {label: 'Divide (/)', value: '/'},
                        ]"
                ></v-select>
              </b-form-group>
            </b-col>

            <!-- Operation Value -->
            <b-col md="12" v-show="show_operator">
              <validation-provider
                name="Operation Value"
                :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('OperationValue')">
                  <b-form-input
                    :placeholder="$t('Enter_Operation_Value')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="Operation-feedback"
                    label="Operation"
                    v-model="unit.operator_value"
                  ></b-form-input>
                  <b-form-invalid-feedback id="Operation-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
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
    title: "Unit"
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
      totalRows: "",
      search: "",
      limit: "10",
      units: [],
      units_base: [],
      editmode: false,
      show_operator: false,
      unit: {
        id: "",
        name: "",
        ShortName: "",
        base_unit: "",
        base_unit_name: "",
        operator: "*",
        operator_value: 1
      }
    };
  },

  computed: {
    columns() {
      return [
        {
          label: this.$t("Name"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("ShortName"),
          field: "ShortName",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("BaseUnit"),
          field: "base_unit_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Operator"),
          field: "operator",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("OperationValue"),
          field: "operator_value",
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
        this.Get_Units(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Units(1);
      }
    },

    //---- Event Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Units(this.serverParams.page);
    },

    //---- Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Units(this.serverParams.page);
    },

    //---- Validation State Form
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation Create & Edit Unit
    Submit_Unit() {
      this.$refs.Create_Unit.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Unit();
          } else {
            this.Update_Unit();
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

    //------------------------------ Modal (create Unit) -------------------------------\\
    New_Unit() {
      this.reset_Form();
      this.show_operator = false;
      this.editmode = false;
      this.$bvModal.show("New_Unit");
    },

    //------------------------------ Modal (Update Unit) -------------------------------\\
    Edit_Unit(unit) {
      this.Get_Units(this.serverParams.page);
      this.reset_Form();
      this.unit = unit;
      if (this.unit.base_unit == "") {
        this.show_operator = false;
      } else {
        this.show_operator = true;
      }
      this.editmode = true;
      this.$bvModal.show("New_Unit");
    },

    Selected_Base_Unit(value) {
      if (value == null) {
        this.show_operator = false;
      } else {
        this.show_operator = true;
      }
    },

    //----------------------------------------  Get All Units -------------------------\\
    Get_Units(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "units?page=" +
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
          this.units = response.data.Units;
          this.totalRows = response.data.totalRows;
          this.units_base = response.data.Units_base;

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

    //---------------------------------------- Set To Strings-------------------------\\
    setToStrings() {
      // Simply replaces null values with strings=''
      if (this.unit.base_unit === null) {
        this.unit.base_unit = "";
      }
    },
    //---------------- Send Request with axios ( Create Unit) --------------------\\
    Create_Unit() {
      this.SubmitProcessing = true;
      this.setToStrings();
      axios
        .post("units", {
          name: this.unit.name,
          ShortName: this.unit.ShortName,
          base_unit: this.unit.base_unit,
          operator: this.unit.operator,
          operator_value: this.unit.operator_value
        })
        .then(response => {
           this.SubmitProcessing = false;
          Fire.$emit("Event_Unit");

          this.makeToast(
            "success",
            this.$t("Create.TitleUnit"),
            this.$t("Success")
          );
        })
        .catch(error => {
           this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //--------------- Send Request with axios ( Update Unit) --------------------\\
    Update_Unit() {
       this.SubmitProcessing = true;
      this.setToStrings();
      axios
        .put("units/" + this.unit.id, {
          name: this.unit.name,
          ShortName: this.unit.ShortName,
          base_unit: this.unit.base_unit,
          operator: this.unit.operator,
          operator_value: this.unit.operator_value
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Unit");

          this.makeToast(
            "success",
            this.$t("Update.TitleUnit"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //------------------------------ reset Form ------------------------------\\
    reset_Form() {
      this.unit = {
        id: "",
        name: "",
        ShortName: "",
        base_unit: "",
        base_unit_name: "",
        operator: "*",
        operator_value: 1
      };
    },

    //--------------------------------- Remove Unit --------------------\\
    Remove_Unit(id) {
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
            .delete("units/" + id)
            .then(response => {
              if (response.data.success) {
                this.$swal(
                  this.$t("Delete.Deleted"),
                  this.$t("Delete.UnitDeleted"),
                  "success"
                );
              } else {
                this.$swal(
                  this.$t("Delete.Failed"),
                  this.$t("Unit_already_linked_with_sub_unit"),
                  "warning"
                );
              }
              Fire.$emit("Delete_Unit");
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

  }, //end Method

  //----------------------------- Created function-------------------
  created: function() {
    this.Get_Units(1);

    Fire.$on("Event_Unit", () => {
      setTimeout(() => {
        this.Get_Units(this.serverParams.page);
        this.$bvModal.hide("New_Unit");
      }, 500);
    });

    Fire.$on("Delete_Unit", () => {
      setTimeout(() => {
        this.Get_Units(this.serverParams.page);
      }, 500);
    });
  }
};
</script>