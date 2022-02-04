<template>
  <div class="main-content">
    <breadcumb :page="$t('SystemSettings')" :folder="$t('Settings')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <!-- System Settings -->
    <validation-observer ref="form_setting" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Setting">
        <b-row>
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('SystemSettings')">
              <b-card-body>
                <b-row>
                  <!-- Default Currency -->
                  <b-col lg="4" md="4" sm="12">
                    <b-form-group :label="$t('DefaultCurrency')">
                      <v-select
                        v-model="setting.currency_id"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Currency')"
                        :options="currencies.map(currencies => ({label: currencies.name, value: currencies.id}))"
                      />
                    </b-form-group>
                  </b-col>

                  <!-- Email  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="Email"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('DefaultEmail')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="Email-feedback"
                          label="Email"
                          :placeholder="$t('DefaultEmail')"
                          v-model="setting.email"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="Email-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- - Logo -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider name="Logo" ref="Logo" rules="mimes:image/*|size:200">
                      <b-form-group
                        slot-scope="{validate, valid, errors }"
                        :label="$t('ChangeLogo')"
                      >
                        <input
                          :state="errors[0] ? false : (valid ? true : null)"
                          :class="{'is-invalid': !!errors.length}"
                          @change="onFileSelected"
                          label="Choose Logo"
                          type="file"
                        >
                        <b-form-invalid-feedback id="Logo-feedback">{{ errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- Company Name  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="Company Name"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('CompanyName')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="Company-feedback"
                          label="Company Name"
                          :placeholder="$t('CompanyName')"
                          v-model="setting.CompanyName"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="Company-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- Company Phone  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="Company Phone"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('CompanyPhone')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="Phone-feedback"
                          label="Company Phone"
                          :placeholder="$t('CompanyPhone')"
                          v-model="setting.CompanyPhone"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="Phone-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- developed by -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="developed by"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('developed_by')">
                         <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="developed_by-feedback"
                          v-model="setting.developed_by"
                          class="form-control"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="developed_by-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                   <!-- Footer -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="footer"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('footer')">
                         <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="footer-feedback"
                          v-model="setting.footer"
                          class="form-control"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="footer-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                   <!-- Default Language -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider name="DefaultLanguage" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('DefaultLanguage')">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="setting.default_language"
                        :reduce="label => label.value"
                        :placeholder="$t('DefaultLanguage')"
                              :options="
                                  [
                                  {label: 'English', value: 'en'},
                                  {label: 'French', value: 'fr'},
                                  {label: 'Arabic', value: 'ar'},
                                  {label: 'Turkish', value: 'tur'},
                                  {label: 'Simplified Chinese', value: 'sm_ch'},
                                  {label: 'Thaï', value: 'thai'},
                                  {label: 'Hindi', value: 'hn'},
                                  {label: 'German', value: 'de'},
                                  {label: 'Spanish', value: 'es'},
                                  {label: 'Italien', value: 'it'},
                                  {label: 'Indonesian', value: 'Ind'},
                                  {label: 'Traditional Chinese', value: 'tr_ch'},
                                  {label: 'Russian', value: 'ru'},
                                  {label: 'Vietnamese', value: 'vn'},
                                  {label: 'Korean', value: 'kr'},
                              ]"                     
                      ></v-select>
                        <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                         </b-form-group>
                    </validation-provider>
                  </b-col>

                  
                    <!-- Default Customer -->
                  <b-col lg="4" md="4" sm="12">
                    <b-form-group :label="$t('DefaultCustomer')">
                      <v-select
                        v-model="setting.client_id"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Customer')"
                        :options="clients.map(clients => ({label: clients.name, value: clients.id}))"
                      />
                    </b-form-group>
                  </b-col>

                   <!-- Default Warehouse -->
                  <b-col lg="4" md="4" sm="12">
                    <b-form-group :label="$t('DefaultWarehouse')">
                      <v-select
                        v-model="setting.warehouse_id"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Warehouse')"
                        :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
                      />
                    </b-form-group>
                  </b-col>

                   <!-- Company Adress -->
                  <b-col lg="12" md="12" sm="12">
                    <validation-provider
                      name="Adress"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('Adress')">
                         <textarea
                          :state="getValidationState(validationContext)"
                          aria-describedby="Adress-feedback"
                          v-model="setting.CompanyAdress"
                          class="form-control"
                          :placeholder="$t('Afewwords')"
                         ></textarea>
                        <b-form-invalid-feedback
                          id="Adress-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>


                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit">{{$t('submit')}}</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>


     <!-- Reciept Pos Settings -->
    <validation-observer ref="Submit_Pos_Settings" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Pos_Settings">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('Pos_Settings')">
              <b-card-body>
                <b-row>

                   <!-- Note to customer  -->
                  <b-col lg="12" md="12" sm="12">
                    <validation-provider
                      name="note"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('Note_to_customer')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="note-feedback"
                          label="Note to customer"
                          :placeholder="$t('Note_to_customer')"
                          v-model="pos_settings.note_customer"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="note-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                 

                   <!-- Show Phone-->
                    <b-col md="4" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Show_Phone')}}
                          <input  type="checkbox" v-model="pos_settings.show_phone">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                     <!-- Show Address -->
                    <b-col md="4" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Show_Address')}}
                          <input  type="checkbox" v-model="pos_settings.show_address">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                      <!-- Show Email  -->
                    <b-col md="4" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Show_Email')}}
                          <input  type="checkbox" v-model="pos_settings.show_email">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                    <!-- Show Customer  -->
                    <b-col md="4" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Show_Customer')}}
                          <input  type="checkbox" v-model="pos_settings.show_customer">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                     <!-- Show Tax & Discount  -->
                    <b-col md="4" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Show_Tax_and_Discount')}}
                          <input  type="checkbox" v-model="pos_settings.show_discount">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                     <!-- Show barcode  -->
                    <b-col md="4" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Show_barcode')}}
                          <input  type="checkbox" v-model="pos_settings.show_barcode">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                      <!-- Show Note_to_customer  -->
                    <b-col md="4" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Show_Note_to_customer')}}
                          <input  type="checkbox" v-model="pos_settings.show_note">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit">{{$t('submit')}}</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>

    <!-- Payment Gateway -->
    <validation-observer ref="form_payment" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Payment">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('Payment_Gateway')">
              <b-card-body>
                <b-row>
                   <!-- Strip key  -->
                  <b-col lg="6" md="6" sm="12">
                      <b-form-group label="STRIPE_KEY">
                        <b-form-input
                           type="password"
                           v-model="gateway.stripe_key"
                            :placeholder="$t('LeaveBlank')"
                        ></b-form-input>
                      </b-form-group>
                  </b-col>

                   <!-- Strip Secret  -->
                  <b-col lg="6" md="6" sm="12">
                      <b-form-group label="STRIPE_SECRET">
                        <b-form-input
                          type="password"
                          v-model="gateway.stripe_secret"
                           :placeholder="$t('LeaveBlank')"
                        ></b-form-input>
                      </b-form-group>
                  </b-col>

                   <!-- Remove Stripe Key & Secret-->
                    <b-col md="6" class="mt-3 mb-3">
                       <label class="switch switch-primary mr-3">
                         {{$t('Remove_Stripe_Key_Secret')}}
                          <input  type="checkbox" v-model="gateway.deleted">
                          <span class="slider"></span>
                        </label>
                    </b-col>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit">{{$t('submit')}}</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>

    <!-- SMS API -->
    <validation-observer ref="form_sms" v-if="!isLoading">
      <b-form @submit.prevent="Submit_sms">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('SMS_Configuration')">
              <b-card-body>
                <b-row>

                  <!-- Gateway  -->
                <b-col md="6">
                  <validation-provider name="Gateway">
                    <b-form-group :label="$t('Gateway')">
                      <v-select
                        v-model="sms.gateway"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Gateway')"
                        :options="
                                [
                                  {label: 'Twilio', value: 'Twilio'},
                                ]"
                      ></v-select>
                    </b-form-group>
                  </validation-provider>
                </b-col>
                  
                   <!-- TWILIO_SID  -->
                  <b-col lg="6" md="6" sm="12">
                    <validation-provider
                      name="TWILIO_SID"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group label="TWILIO_SID">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="TWILIO_SID-feedback"
                          label="TWILIO_SID"
                          v-model="sms.TWILIO_SID"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="TWILIO_SID-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                   <!-- TWILIO_TOKEN  -->
                  <b-col lg="6" md="6" sm="12">
                      <b-form-group label="TWILIO_TOKEN">
                        <b-form-input
                          label="TWILIO_TOKEN"
                          v-model="sms.TWILIO_TOKEN"
                          :placeholder="$t('LeaveBlank')"
                        ></b-form-input>
                      </b-form-group>
                  </b-col>

                    <!-- TWILIO_FROM  -->
                  <b-col lg="6" md="6" sm="12">
                    <validation-provider
                      name="TWILIO_FROM"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group label="TWILIO_FROM">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="TWILIO_FROM-feedback"
                          label="TWILIO_FROM"
                          v-model="sms.TWILIO_FROM"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="TWILIO_FROM-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit">{{$t('submit')}}</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>

    <!-- Server SMTP -->
    <validation-observer ref="form_smtp" v-if="!isLoading">
      <b-form @submit.prevent="Submit_SMTP">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('SMTPConfiguration')">
              <b-card-body>
                <b-row>
                  <!-- HOST  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="HOST"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('HOST')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="HOST-feedback"
                          label="HOST"
                          :placeholder="$t('HOST')"
                          v-model="server.host"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="HOST-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- PORT  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="PORT"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('PORT')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="PORT-feedback"
                          label="PORT"
                          :placeholder="$t('PORT')"
                          v-model="server.port"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="PORT-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- Username  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="Username"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('username')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="Username-feedback"
                          label="Username"
                          :placeholder="$t('username')"
                          v-model="server.username"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="Username-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- Password  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="Password"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('password')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="Password-feedback"
                          label="Password"
                          :placeholder="$t('password')"
                          v-model="server.password"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="Password-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <!-- encryption  -->
                  <b-col lg="4" md="4" sm="12">
                    <validation-provider
                      name="encryption"
                      :rules="{ required: true}"
                      v-slot="validationContext"
                    >
                      <b-form-group :label="$t('encryption')">
                        <b-form-input
                          :state="getValidationState(validationContext)"
                          aria-describedby="encryption-feedback"
                          label="encryption"
                          :placeholder="$t('encryption')"
                          v-model="server.encryption"
                        ></b-form-input>
                        <b-form-invalid-feedback
                          id="encryption-feedback"
                        >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                      </b-form-group>
                    </validation-provider>
                  </b-col>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" type="submit">{{$t('submit')}}</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>

    <!-- Clear Cache -->
      <b-form @submit.prevent="Clear_Cache" v-if="!isLoading">
        <b-row class="mt-5">
          <b-col lg="12" md="12" sm="12">
            <b-card no-body :header="$t('Clear_Cache')">
              <b-card-body>
                <b-row>

                  <b-col md="12">
                    <b-form-group>
                      <b-button variant="primary" @click="Clear_Cache()" >{{$t('Clear_Cache')}}</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "System Settings"
  },
  data() {
    return {
      
      isLoading: true,
      data: new FormData(),
      settings: [],
      currencies: [],
      clients: [],
      warehouses: [],
      setting: {
        client_id: "",
        warehouse_id: "",
        currency_id: "",
        email: "",
        logo: "",
        CompanyName: "",
        CompanyPhone: "",
        CompanyAdress: "",
        footer:"",
        developed_by:"",
        default_language:"",
      },

      pos_settings:{
        note_customer:"",
        show_note:"",
        show_barcode:"",
        show_discount:"",
        show_phone:"",
        show_email:"",
        show_address:"",
        show_customer:"",
      },

      gateway:{
        stripe_key:"",
        stripe_secret:"",
        deleted:false,
      },
      sms:{
        gateway:'Twilio',
        TWILIO_SID:'',
        TWILIO_TOKEN:'',
        TWILIO_FROM:'',
      },
      server: {
        host: "",
        port: "",
        username: "",
        password: "",
        encryption: ""
      }
    };
  },

  methods: {
    ...mapActions(["refreshUserPermissions"]),


      SetLocal(locale) {
      this.$i18n.locale = locale;
      this.$store.dispatch("language/setLanguage", locale);
      Fire.$emit("ChangeLanguage");
    },

    //------------- Submit Validation Setting
    Submit_Setting() {
      this.$refs.form_setting.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          this.Update_Settings();
        }
      });
    },


     //------------- Submit Validation Pos Setting
    Submit_Pos_Settings() {
      this.$refs.Submit_Pos_Settings.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          this.Update_Pos_Settings();
        }
      });
    },

    //------------- Submit Validation SMTP
    Submit_SMTP() {
      this.$refs.form_smtp.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
            this.Update_Smtp();
        }
      });
    },

     //------------- Submit Validation Payment
    Submit_Payment() {
      this.$refs.form_payment.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
            this.Update_Payment();
        }
      });
    },

      //------------- Submit Validation SMS
    Submit_sms() {
      this.$refs.form_sms.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
            this.Update_SMS();
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

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------------------------ Event Upload Logo -------------------------------\\
    async onFileSelected(e) {
      const { valid } = await this.$refs.Logo.validate(e);

      if (valid) {
        this.setting.logo = e.target.files[0];
      } else {
        this.setting.logo = "";
      }
    },


  
    //---------------------------------- Update Settings ----------------\\
    Update_Settings() {
      NProgress.start();
      NProgress.set(0.1);
      var self = this;
      self.data.append("client", self.setting.client_id);
      self.data.append("warehouse", self.setting.warehouse_id);
      self.data.append("currency", self.setting.currency_id);
      self.data.append("email", self.setting.email);
      self.data.append("logo", self.setting.logo);
      self.data.append("CompanyName", self.setting.CompanyName);
      self.data.append("CompanyPhone", self.setting.CompanyPhone);
      self.data.append("CompanyAdress", self.setting.CompanyAdress);
      self.data.append("footer", self.setting.footer);
      self.data.append("developed_by", self.setting.developed_by);
      self.data.append("default_language", self.setting.default_language);
      self.data.append("_method", "put");

      axios
        .post("settings/" + self.setting.id, self.data)
        .then(response => {
          Fire.$emit("Event_Setting");
          this.makeToast(
            "success",
            this.$t("Successfully_Updated"),
            this.$t("Success")
          );
          this.refreshUserPermissions();
          NProgress.done();
          this.SetLocal(self.setting.default_language);
        })
        .catch(error => {
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          NProgress.done();
        });
    }, 


      //---------------------------------- Update_Pos_Settings ----------------\\
    Update_Pos_Settings() {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .put("pos_settings/" + this.pos_settings.id, {
          note_customer: this.pos_settings.note_customer,
          show_note: this.pos_settings.show_note,
          show_barcode: this.pos_settings.show_barcode,
          show_discount: this.pos_settings.show_discount,
          show_phone: this.pos_settings.show_phone,
          show_email: this.pos_settings.show_email,
          show_address: this.pos_settings.show_address,
          show_customer: this.pos_settings.show_customer,      
        })
        .then(response => {
          Fire.$emit("Event_Pos_Settings");
          this.makeToast(
            "success",
            this.$t("Successfully_Updated"),
            this.$t("Success")
          );
          NProgress.done();
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------------------- Update SMTP ----------------\\
    Update_Smtp() {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .put("SMTP/" + this.server.id, {
          host: this.server.host,
          port: this.server.port,
          username: this.server.username,
          password: this.server.password,
          encryption: this.server.encryption
        })
        .then(response => {
          Fire.$emit("Event_Smtp");
          this.makeToast(
            "success",
            this.$t("Successfully_Updated"),
            this.$t("Success")
          );
          NProgress.done();
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------------------- Update Payment Gateway ----------------\\
    Update_Payment() {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("payment_gateway",{
          stripe_key: this.gateway.stripe_key,
          stripe_secret: this.gateway.stripe_secret,
          deleted: this.gateway.deleted,
        })
        .then(response => {
          Fire.$emit("Event_payment");
          this.makeToast(
            "success",
            this.$t("Successfully_Updated"),
            this.$t("Success")
          );
          NProgress.done();
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

     //---------------------------------- Update sms ----------------\\
    Update_SMS() {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .post("sms_config",{
          TWILIO_SID: this.sms.TWILIO_SID,
          TWILIO_TOKEN: this.sms.TWILIO_TOKEN,
          TWILIO_FROM: this.sms.TWILIO_FROM,
        })
        .then(response => {
          Fire.$emit("Event_sms");
          this.makeToast(
            "success",
            this.$t("Successfully_Updated"),
            this.$t("Success")
          );
          NProgress.done();
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

     //---------------------------------- get_sms_config ----------------\\
    get_sms_config() {
      axios
        .get("get_sms_config")
        .then(response => {
          this.sms = response.data.sms;
        })
        .catch(error => {
        });
    },   


    //---------------------------------- Clear_Cache ----------------\\
    Clear_Cache() {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("Clear_Cache")
        .then(response => {
          this.makeToast(
            "success",
            this.$t("Cache_cleared_successfully"),
            this.$t("Success")
          );
          NProgress.done();
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("Failed_to_clear_cache"), this.$t("Failed"));
        });
    },   

     //---------------------------------- GET Payment_Gateway ----------------\\
    Get_Payment_Gateway() {
      axios
        .get("Get_payment_gateway")
        .then(response => {
          this.gateway = response.data.gateway;
        })
        .catch(error => {
        });
    },

    //---------------------------------- Get SETTINGS ----------------\\
    Get_Settings() {
      axios
        .get("getSettings")
        .then(response => {
          this.setting    = response.data.settings;
          this.currencies = response.data.currencies;
          this.clients    = response.data.clients;
          this.warehouses = response.data.warehouses;
          this.Get_SMTP();
          this.get_pos_Settings();
          this.Get_Payment_Gateway();
          this.get_sms_config();
          this.isLoading = false;
        })
        .catch(error => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //---------------------------------- GET SMTP ----------------\\ 
    Get_SMTP() {
      axios
        .get("getSMTP")
        .then(response => {
          this.server = response.data.server;
        })
        .catch(error => {
        });
    },


 //---------------------------------- Get_pos_Settings ----------------\\ 
    get_pos_Settings() {
      axios
        .get("get_pos_Settings")
        .then(response => {
          this.pos_settings = response.data.pos_settings;
        })
        .catch(error => {
        });
    },

   
  }, //end Methods

  //----------------------------- Created function-------------------

  created: function() {
    this.Get_Settings();

    Fire.$on("Event_Smtp", () => {
      this.Get_SMTP(); 
    });

     Fire.$on("Event_payment", () => {
      this.Get_Payment_Gateway();
    });

    Fire.$on("Event_Setting", () => {
      this.Get_Settings();
    });

     Fire.$on("Event_Pos_Settings", () => {
      this.get_pos_Settings();
    });

    Fire.$on("Event_sms", () => {
      this.get_sms_config();
    });
  }
};
</script>