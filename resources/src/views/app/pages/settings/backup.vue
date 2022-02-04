<template>
  <div class="main-content">
    <breadcumb :page="$t('BackupDatabase')" :folder="$t('Settings')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <span class="alert alert-danger">{{$t('You_will_find_your_backup_on')}} <strong>/storage/app/public/backup</strong> {{$t('and_save_it_to_your_pc')}}</span>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="backups"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button
            @click="GenerateBackup()"
            size="sm"
            class="btn-rounded"
            variant="btn btn-primary btn-icon m-1"
          >
            <i class="i-Add"></i>
            {{$t('GenerateBackup')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <!-- <a v-b-tooltip.hover @click="DownloadBackup(props.row.date)" title="Download">
              <i class="i-Download text-25 text-success"></i>
            </a> -->
            <a title="Delete" v-b-tooltip.hover @click="DeleteBackup(props.row.date)">
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </b-card>
  </div>
</template>



<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Backup"
  },
  data() {
    return {
      backups: [],
      isLoading: true,
      totalRows: ""
    };
  },

  computed: {
    columns() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Filesize"),
          field: "size",
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
    //---------------------------------- Generate Backup --------------------\\

    GenerateBackup() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("GenerateBackup")
        .then(response => {
          Fire.$emit("Generate_Backup");
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        })
        .catch(response => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

  
    //----------------------------------------  Get All backups -------------------------\\
    Get_Backups() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("GetBackup")
        .then(response => {
          this.backups = response.data.backups;
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

    //--------------------------------- Delete Backup --------------------\\
    DeleteBackup(date) {
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
            .delete("DeleteBackup/" + date)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Delete.BackupDeleted"),
                "success"
              );

              Fire.$emit("Delete_Backup");
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
  }, //end Method

  //----------------------------- Created function-------------------
  created: function() {
    this.Get_Backups();

    Fire.$on("Generate_Backup", () => {
      setTimeout(() => {
        this.Get_Backups();
      }, 500);
    });

    Fire.$on("Delete_Backup", () => {
      setTimeout(() => {
        this.Get_Backups();
        // Complete the animation of the  progress bar.
        NProgress.done();
      }, 500);
    });
  }
};
</script>