<template>
  <div class="main-content">
    <breadcumb :page="$t('DetailQuote')" :folder="$t('ListQuotations')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <b-card v-if="!isLoading">
      <b-row>
        <b-col md="12" class="mb-5">
          <router-link
            v-if="currentUserPermissions && currentUserPermissions.includes('Quotations_edit')"
            title="Edit"
            class="btn-sm btn btn-success ripple btn-icon m-1"
            :to="{ name:'edit_quotation', params: { id: $route.params.id } }"
          >
            <i class="i-Edit"></i>
            <span>{{$t('EditQuote')}}</span>
          </router-link>
          <router-link
            v-if="quote.statut && currentUserPermissions && currentUserPermissions.includes('Quotations_edit')"
            title="Create"
            class="btn-sm btn btn-primary ripple btn-icon m-1"
            :to="{ name:'change_to_sale', params: { id:$route.params.id } }"
          >
            <i class="i-Add"></i>
            <span>{{$t('CreateSale')}}</span>
          </router-link>
          <button @click="Quote_Email()" class="btn-sm btn btn-info ripple btn-icon m-1">
            <i class="i-Envelope-2"></i>
            {{$t('Email')}}
          </button>
          <button @click="Quote_SMS()" class="btn btn-secondary btn-icon ripple btn-sm">
            <i class="i-Speach-Bubble"></i>
            SMS
          </button>
          <button @click="Quote_PDF()" class="btn-sm btn btn-light ripple btn-icon m-1">
            <i class="i-File-TXT"></i> PDF
          </button>
          <button @click="print()" class="btn-sm btn btn-warning ripple btn-icon m-1">
            <i class="i-Billing"></i>
            {{$t('print')}}
          </button>
          <button
            v-if="currentUserPermissions && currentUserPermissions.includes('Quotations_delete')"
            @click="Remove_Quote()"
            class="btn btn-danger btn-icon icon-left btn-sm m-1"
          >
            <i class="i-Close-Window"></i>
            {{$t('Del')}}
          </button>
        </b-col>
      </b-row>
      <div class="invoice" id="print_Invoice">
        <div class="invoice-print">
          <b-row class="justify-content-md-center">
            <h4 class="font-weight-bold">{{$t('DetailQuote')}} : {{quote.Ref}}</h4>
          </b-row>
          <hr>
          <b-row class="mt-5">
            <b-col md="4" class="mb-4">
              <h5 class="font-weight-bold">{{$t('Customer_Info')}}</h5>
              <div>{{quote.client_name}}</div>
              <div>{{quote.client_email}}</div>
              <div>{{quote.client_phone}}</div>
              <div>{{quote.client_adr}}</div>
            </b-col>
            <b-col md="4" class="mb-4">
              <h5 class="font-weight-bold">{{$t('Company_Info')}}</h5>
              <div>{{company.CompanyName}}</div>
              <div>{{company.email}}</div>
              <div>{{company.CompanyPhone}}</div>
              <div>{{company.CompanyAdress}}</div>
            </b-col>
            <b-col md="4" class="mb-4">
              <h5 class="font-weight-bold">{{$t('Quote_Info')}}</h5>
              <div>{{$t('Reference')}} : {{quote.Ref}}</div>
              <div>
                {{$t('Status')}} :
                <span
                  v-if="quote.statut == 'sent'"
                  class="badge badge-outline-success"
                >{{$t('Sent')}}</span>
                <span v-else class="badge badge-outline-warning">{{$t('Pending')}}</span>
              </div>
              <div>{{$t('date')}} : {{quote.date}}</div>
              <div>{{$t('warehouse')}} : {{quote.warehouse}}</div>
            </b-col>
          </b-row>
          <b-row class="mt-3">
            <b-col md="12">
              <h5 class="font-weight-bold">{{$t('Order_Summary')}}</h5>
              <div class="table-responsive">
                <table class="table table-hover table-md">
                  <thead class="bg-gray-300">
                    <tr>
                      <th scope="col">{{$t('ProductName')}}</th>
                      <th scope="col">{{$t('Net_Unit_Price')}}</th>
                      <th scope="col">{{$t('Quantity')}}</th>
                      <th scope="col">{{$t('UnitPrice')}}</th>
                      <th scope="col">{{$t('Discount')}}</th>
                      <th scope="col">{{$t('Tax')}}</th>
                      <th scope="col">{{$t('SubTotal')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="detail in details">
                      <td>{{detail.code}} ({{detail.name}})</td>
                      <td>{{currentUser.currency}} {{formatNumber(detail.Net_price,3)}}</td>
                      <td>{{formatNumber(detail.quantity,2)}} {{detail.unit_sale}}</td>
                      <td>{{currentUser.currency}} {{formatNumber(detail.price,2)}}</td>
                      <td>{{currentUser.currency}} {{formatNumber(detail.DiscountNet,2)}}</td>
                      <td>{{currentUser.currency}} {{formatNumber(detail.taxe,2)}}</td>
                      <td>{{currentUser.currency}} {{detail.total.toFixed(2)}}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </b-col>
            <div class="offset-md-9 col-md-3 mt-4">
              <table class="table table-striped table-sm">
                <tbody>
                  <tr>
                    <td class="bold">{{$t('OrderTax')}}</td>
                    <td>
                      <span>{{currentUser.currency}} {{quote.TaxNet.toFixed(2)}} ({{formatNumber(quote.tax_rate ,2)}} %)</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="bold">{{$t('Discount')}}</td>
                    <td>{{currentUser.currency}} {{quote.discount.toFixed(2)}}</td>
                  </tr>
                  <tr>
                    <td class="bold">{{$t('Shipping')}}</td>
                    <td>{{currentUser.currency}} {{quote.shipping.toFixed(2)}}</td>
                  </tr>
                  <tr>
                    <td>
                      <span class="font-weight-bold">{{$t('Total')}}</span>
                    </td>
                    <td>
                      <span
                        class="font-weight-bold"
                      >{{currentUser.currency}} {{quote.GrandTotal}}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </b-row>
           <hr v-show="quote.note">
          <b-row class="mt-4">
           <b-col md="12">
             <p>{{quote.note}}</p>
           </b-col>
        </b-row>
        </div>
      </div>
    </b-card>
  </div>
</template>


<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  computed: mapGetters(["currentUserPermissions", "currentUser"]),
  metaInfo: {
    title: "Detail Quotation"
  },

  data() {
    return {
      isLoading: true,
      quote: {},
      details: [],
      variants: [],
      company: {},
      email: {
        to: "",
        subject: "",
        message: "",
        client_name: "",
        Quote_Ref: ""
      }
    };
  },

  methods: {
    //------------------------------ Print -------------------------\\
    print() {
      var divContents = document.getElementById("print_Invoice").innerHTML;
      var a = window.open("", "", "height=500, width=500");
      a.document.write(
        '<link rel="stylesheet" href="/assets_setup/css/bootstrap.css"><html>'
      );
      a.document.write("<body >");
      a.document.write(divContents);
      a.document.write("</body></html>");
      a.document.close();
      a.print();
    },

    //----------------------------------- Print Quotation -------------------------\\
    Quote_PDF() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
     
       axios
        .get(`Quote_PDF/${id}`, {
          responseType: "blob", // important
          headers: {
            "Content-Type": "application/json"
          }
        })
        .then(response => {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", "Quotation_" + this.quote.Ref + ".pdf");
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

    //----------------------------------- Get Details Product ------------------------------\\
    Get_Details() {
      let id = this.$route.params.id;
      axios
        .get(`quotations/${id}`)
        .then(response => {
          this.quote = response.data.quote;
          this.details = response.data.details;
          this.company = response.data.company;
          this.isLoading = false;
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
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

    //------------------------------------ Form Send Quotation in Email -------------------------\\
    Quote_Email() {
      this.email.to = this.quote.client_email;
      this.email.Quote_Ref = this.quote.Ref;
      this.email.client_name = this.quote.client_name;
      this.SendEmail();
    },
    SendEmail() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .post("quotations/sendQuote/email", {
          id: id,
          to: this.email.to,
          client_name: this.email.client_name,
          Ref: this.email.Quote_Ref
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

    //---------SMS notification
     
     Quote_SMS() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .post("quotations/send/sms", {
          id: id,
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

    //-------------------------------------------- Delete Quotation -------------------------\\
    Remove_Quote() {
      let id = this.$route.params.id;
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
            .delete("quotations/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.QuoteDeleted"),
                "success"
              );
              this.$router.push({ name: "index_quotation" });
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
    }
  }, //end Methods

  //----------------------------- Created function-------------------

  created: function() {
    this.Get_Details();
  }
};
</script>