<template>
  <div class="main-content">
    <breadcumb :page="$t('ProfitandLoss')" :folder="$t('Reports')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-row v-if="!isLoading">
      <div class="table-responsive">
        <b-col md="12">
          <date-range-picker
            :from="$route.query.from"
            :to="$route.query.to"
            :panel="$route.query.panel"
            @update="Submit"
          />
        </b-col>
        <b-col md="12" class="mt-4">
          <b-row>
            <!-- /.Total Sales -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">
                    <span class="bold">{{infos.sales.nmbr?infos.sales.nmbr:0}}</span>
                    {{$t('Sales')}}
                  </p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.sales.sum?infos.sales.sum:0),2)}}</p>
                </div>
              </div>
            </b-col>
            <!-- /.col -->
            <!-- /.Total Purchases -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">
                    <span class="bold">{{infos.purchases.nmbr?infos.purchases.nmbr:0}}</span>
                    {{$t('Purchases')}}
                  </p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.purchases.sum?infos.purchases.sum:0),2)}}</p>
                </div>
              </div>
            </b-col>
            <!-- /.col -->
            <!-- /.Total Returns Sales -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">
                    <span class="bold">{{infos.returns_sales.nmbr?infos.returns_sales.nmbr:0}}</span>
                    {{$t('SalesReturn')}}
                  </p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.returns_sales.sum?infos.returns_sales.sum:0),2)}}</p>
                </div>
              </div>
            </b-col>
            <!-- /.col -->
            <!-- /.Total Returns Purchases -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">
                    <span
                      class="bold"
                    >{{infos.returns_purchases.nmbr?infos.returns_purchases.nmbr:0}}</span>
                    {{$t('PurchasesReturn')}}
                  </p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.returns_purchases.sum?infos.returns_purchases.sum:0),2)}}</p>
                </div>
              </div>
            </b-col>

            <!-- /.col -->
            <!-- /.Expense -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">
                    <span class="bold">{{$t('Expenses')}}</span>
                  </p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.expenses.sum?infos.expenses.sum:0),2)}}</p>
                </div>
              </div>
            </b-col>

            <!-- /.col -->
            <!-- /.Profit & Loss -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">{{$t('Profit')}}</p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.profit?infos.profit:0),2)}}</p>
                </div>

                <div class="card-footer">
                  <p>
                    (
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.sales.sum?infos.sales.sum:0),2)}}</span>
                    {{$t('Sales')}})
                    - (
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.purchases.sum?infos.purchases.sum:0),2)}}</span>
                    {{$t('Purchases')}})
                    
                    <!-- <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.returns_sales.sum?infos.returns_sales.sum:0),2)}}</span>
                    {{$t('SalesReturn')}})
                    - (
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.expenses.sum?infos.expenses.sum:0),2)}}</span>
                    {{$t('Expenses')}})
                    + (
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.returns_purchases.sum?infos.returns_purchases.sum:0),2)}}</span>
                    {{$t('PurchasesReturn')}}) -->
                  </p>
                </div>
              </div>
            </b-col>

            <!-- /.Paiements Received -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">{{$t('PaiementsReceived')}}</p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.payment_received?infos.payment_received:0),2)}}</p>
                </div>

                <div class="card-footer">
                  <p>
                    (
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.paiement_sales.sum?infos.paiement_sales.sum:0),2)}}</span>
                    {{$t('PaymentsSales')}}
                    +
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.PaymentPurchaseReturns.sum?infos.PaymentPurchaseReturns.sum:0),2)}}</span>
                    {{$t('PurchasesReturn')}})
                  </p>
                </div>
              </div>
            </b-col>
            <!-- /.col -->

            <!-- /.Paiements Sent -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">{{$t('PaiementsSent')}}</p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.payment_sent?infos.payment_sent:0),2)}}</p>
                </div>
                <div class="card-footer">
                  <p>
                    (
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.paiement_purchases.sum?infos.paiement_purchases.sum:0),2)}}</span>
                    {{$t('PaymentsPurchases')}}
                    +
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.PaymentSaleReturns.sum?infos.PaymentSaleReturns.sum:0),2)}}</span>
                    {{$t('SalesReturn')}})
                    +
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.expenses.sum?infos.expenses.sum:0),2)}}</span>
                    {{$t('Expenses')}})
                  </p>
                </div>
              </div>
            </b-col>
            <!-- /.col -->

            <!-- /.Paiements Net -->
            <b-col md="4" sm="12">
              <div class="card card-icon text-center mb-30">
                <div class="card-body">
                  <i class="i-Data-Upload"></i>
                  <p class="text-muted mt-2 mb-2">{{$t('PaiementsNet')}}</p>
                  <p
                    class="text-primary text-24 line-height-1 m-0"
                  >{{currentUser.currency}} {{formatNumber((infos.paiement_net?infos.paiement_net:0),2)}}</p>
                </div>
                <div class="card-footer">
                  <p>
                    (
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.payment_received?infos.payment_received:0),2)}}</span>
                    {{$t('Recieved')}}
                    -
                    <span
                      class="bold"
                    >{{currentUser.currency}} {{formatNumber((infos.payment_sent?infos.payment_sent:0),2)}}</span>
                    {{$t('Sent')}})
                  </p>
                </div>
              </div>
            </b-col>
            <!-- /.col -->
          </b-row>
        </b-col>
      </div>
    </b-row>
  </div>
</template>


<script>
import NProgress from "nprogress";
import { mapActions, mapGetters } from "vuex";

export default {
  metaInfo: {
    title: "Profit & Loss"
  },
  data() {
    return {
      isLoading: true,
      infos: [],
      today: new Date().toJSON().slice(0, 10)
    };
  },

  computed: {
    ...mapGetters(["currentUser"])
  },

  methods: {
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

    //----------------------------- Submit Date Picker -------------------\\
    Submit(values) {
      var self = this;
      self.ProfitAndLoss(values);
    },

    //----------------------------- Profit And Loss-------------------\\
    ProfitAndLoss(values) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "report/ProfitAndLoss?to=" +
            values.to.substring(0, 10) +
            "&from=" +
            values.from.substring(0, 10)
        )
        .then(response => {
          this.infos = response.data.data;
          // Complete the animation of theprogress bar.
          NProgress.done();
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
        });
    },

    getData() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);

      axios
        .get("report/ProfitAndLoss?to=" + this.today + "&from=" + this.today)
        .then(response => {
          this.infos = response.data.data;
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
    }
  }, //end Methods

  //-----------------------------Autoload function-------------------\\

  created: function() {
    this.getData();
  }
};
</script>