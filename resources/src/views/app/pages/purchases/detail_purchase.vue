<template>
  <div class="main-content">
    <breadcumb :page="$t('PurchaseDetail')" :folder="$t('ListPurchases')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <b-card v-if="!isLoading">
      <b-row>
        <b-col md="12" class="mb-2">
          <router-link
            v-if="currentUserPermissions && currentUserPermissions.includes('Purchases_edit')"
            title="Edit"
            class="btn btn-success btn-icon ripple btn-sm"
            :to="{ name:'edit_purchase', params: { id: $route.params.id } }"
          >
            <i class="i-Edit"></i>
            <span>{{$t('EditPurchase')}}</span>
          </router-link>
          <button @click="purchase_Email()" class="btn btn-info btn-icon ripple btn-sm">
            <i class="i-Envelope-2"></i>
            {{$t('Email')}}
          </button>
           <button @click="Purchase_SMS()" class="btn btn-secondary btn-icon ripple btn-sm">
            <i class="i-Speach-Bubble"></i>
            SMS
          </button>
          <button @click="Print_Purchase_PDF()" class="btn btn-primary btn-icon ripple btn-sm">
            <i class="i-File-TXT"></i> PDF
          </button>
          <button @click="print()" class="btn btn-warning btn-icon ripple btn-sm">
            <i class="i-Billing"></i>
            {{$t('print')}}
          </button>
          <button
            v-if="currentUserPermissions && currentUserPermissions.includes('Purchases_delete')"
            @click="Delete_Purchase()"
            class="btn btn-danger btn-icon ripple btn-sm"
          >
            <i class="i-Close-Window"></i>
            {{$t('Del')}}
          </button>
        </b-col>
      </b-row>
      <div class="invoice mt-5" id="print_Invoice">
        <div class="invoice-print">
          <b-row class="justify-content-md-center">
            <h4 class="font-weight-bold">{{$t('PurchaseDetail')}} : {{purchase.Ref}}</h4>
          </b-row>
          <hr>
          <b-row class="mt-5">
            <b-col lg="4" md="4" sm="12" class="mb-4">
              <h5 class="font-weight-bold">{{$t('Supplier_Info')}}</h5>
              <div>{{purchase.supplier_name}}</div>
              <div>{{purchase.supplier_email}}</div>
              <div>{{purchase.supplier_phone}}</div>
              <div>{{purchase.supplier_adr}}</div>
            </b-col>
            <b-col lg="4" md="4" sm="12" class="mb-4">
              <h5 class="font-weight-bold">{{$t('Company_Info')}}</h5>
              <div>{{company.CompanyName}}</div>
              <div>{{company.email}}</div>
              <div>{{company.CompanyPhone}}</div>
              <div>{{company.CompanyAdress}}</div>
            </b-col>
            <b-col lg="4" md="4" sm="12" class="mb-4">
              <h5 class="font-weight-bold">{{$t('Purchase_Info')}}</h5>
              <div>{{$t('Reference')}} : {{purchase.Ref}}</div>
              <div>
                {{$t('Status')}} :
                <span
                  v-if="purchase.statut == 'received'"
                  class="badge badge-outline-success"
                >{{$t('Received')}}</span>
                <span
                  v-else-if="purchase.statut == 'pending'"
                  class="badge badge-outline-info"
                >{{$t('Pending')}}</span>
                <span v-else class="badge badge-outline-warning">{{$t('Ordered')}}</span>
              </div>
              <div>{{$t('warehouse')}} : {{purchase.warehouse}}</div>
              <div>
                {{$t('PaymentStatus')}} :
                <span
                  v-if="purchase.payment_status == 'paid'"
                  class="badge badge-outline-success"
                >{{$t('Paid')}}</span>
                <span
                  v-else-if="purchase.payment_status == 'partial'"
                  class="badge badge-outline-primary"
                >{{$t('partial')}}</span>
                <span v-else class="badge badge-outline-warning">{{$t('Unpaid')}}</span>
              </div>
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
                      <th scope="col">{{$t('Net_Unit_Cost')}}</th>
                      <th scope="col">{{$t('Quantity')}}</th>
                      <th scope="col">{{$t('Unitcost')}}</th>
                      <th scope="col">{{$t('Discount')}}</th>
                      <th scope="col">{{$t('Tax')}}</th>
                      <th scope="col">{{$t('SubTotal')}}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="detail in details">
                      <td>{{detail.code}} ({{detail.name}})</td>
                      <td>{{currentUser.currency}} {{formatNumber(detail.Net_cost,3)}}</td>
                      <td>{{formatNumber(detail.quantity,2)}} {{detail.unit_purchase}}</td>
                      <td>{{currentUser.currency}} {{formatNumber(detail.cost,2)}}</td>
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
                      <span>{{currentUser.currency}} {{purchase.TaxNet.toFixed(2)}} ({{formatNumber(purchase.tax_rate,2)}} %)</span>
                    </td>
                  </tr>
                  <tr>
                    <td class="bold">{{$t('Discount')}}</td>
                    <td>{{currentUser.currency}} {{purchase.discount.toFixed(2)}}</td>
                  </tr>
                  <tr>
                    <td class="bold">{{$t('Shipping')}}</td>
                    <td>{{currentUser.currency}} {{purchase.shipping.toFixed(2)}}</td>
                  </tr>
                  <tr>
                    <td>
                      <span class="font-weight-bold">{{$t('Total')}}</span>
                    </td>
                    <td>
                      <span
                        class="font-weight-bold"
                      >{{currentUser.currency}} {{purchase.GrandTotal}}</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <span class="font-weight-bold">{{$t('Paid')}}</span>
                    </td>
                    <td>
                      <span
                        class="font-weight-bold"
                      >{{currentUser.currency}} {{purchase.paid_amount}}</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <span class="font-weight-bold">{{$t('Due')}}</span>
                    </td>
                    <td>
                      <span
                        class="font-weight-bold"
                      >{{currentUser.currency}} {{purchase.due}}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </b-row>
          <hr v-show="purchase.note">
          <b-row class="mt-4">
           <b-col md="12">
             <p>{{purchase.note}}</p>
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
    title: "Detail Purchase"
  },

  data() {
    return {
      isLoading: true,
      purchase: {},
      details: [],
      variants: [],
      company: {},
      email: {
        to: "",
        subject: "",
        message: "",
        supplier_name: "",
        Purchase_Ref: ""
      }
    };
  },

  methods: {
    //----------------------------------- print Purchase -------------------------\\
    Print_Purchase_PDF() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .get(`Purchase_PDF/${id}`, {
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
            "purchase_" + this.purchase.Ref + ".pdf"
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

    //----------------------------------- Get Details Purchase ------------------------------\\
    Get_Details() {
      let id = this.$route.params.id;
      axios
        .get(`purchases/${id}`)
        .then(response => {
          this.purchase = response.data.purchase;
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

    //---------------------------------------------------- Purchase Email -------------------------------\\
    purchase_Email(purchase) {
      this.email.to = this.purchase.supplier_email;
      this.email.Purchase_Ref = this.purchase.Ref;
      this.email.supplier_name = this.purchase.supplier_name;
      this.Send_Email();
    },

    //--------------------------------------------- Send Purchase to Email -------------------------------\\
    Send_Email() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .post("purchases/send/email", {
          id: id,
          to: this.email.to,
          supplier_name: this.email.supplier_name,
          Ref: this.email.Purchase_Ref
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
     
     Purchase_SMS() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .post("purchases/send/sms", {
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

      //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },


    //------- Remove Purchase

    Delete_Purchase() {
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
            .delete("purchases/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.PurchaseDeleted"),
                "success"
              );
              this.$router.push({ name: "index_purchases" });
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