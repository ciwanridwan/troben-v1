const main = {
  home: {
    icon: "home",
    text: "Home",
    route: "admin.home",
    children: {
      all: {
        title: "All Order",
        text: "Data Order",
        route: "admin.home",
        children: null
      },
      resi: {
        title: "All Resi",
        text: "Resi",
        route: "admin.home.receipt",
        children: null
      },
      manifest: {
        title: "All Manifest",
        text: "Manifest",
        route: "admin.home.manifest",
        children: {
          all: {
            title: "All Manifest",
            text: "All Manifest",
            route: "admin.home.manifest",
            children: null
          },
          // tracking: {
          //   title: "Tracking Manifest",
          //   text: "Tracking Manifest",
          //   route: "admin.home.manifest.tracking",
          //   children: null
          // },
          request: {
            title: "Request Transporter",
            text: "Request Transporter",
            route: "admin.home.manifest.request.transporter",
            children: null
          }
        }
      },
      delivery: {
        title: "All Delivery Order",
        text: "Data Delivery Order",
        route: "admin.home.delivery",
        children: null
      },
      accountExecutive: {
        title: "Account Executive",
        text: "Account Executive",
        route: "admin.home.accountexecutive",
        children: {
          all: {
            title: "Akun Agen Trawlbens",
            text: "Akun Agen Trawlbens",
            route: "admin.home.accountexecutive",
            children: null
          },
          request: {
            title: "Tim Agen Trawlbens",
            text: "Tim Agen Trawlbens",
            route: "admin.home.accountexecutive.teamagent",
            children: null
          }
        }
      },
    },
    shortKey: ["ctrl", "alt", "d"]
  },
  history: {
    icon: "history",
    text: "Riwayat",
    route: "admin.history.pending",
    children: {
      pending: {
        title: "All Order",
        text: "Menunggu Konfirmasi Admin",
        route: "admin.history.pending",
        children: null
      },
      cancel: {
        title: "All Cancel Order",
        text: "Data Cancel Order",
        route: "admin.history.cancel",
        children: null
      },
      paid: {
        title: "All Lunas Order",
        text: "Data Lunas Customer",
        route: "admin.history.paid",
        children: null
      }
    },
    shortKey: ["ctrl", "alt", "h"]
  },

  payment: {
    icon: "wallet",
    text: "Pembayaran",
    route: "admin.payment.home",
    children: {
      home: {
        title: "Home",
        text: "Beranda",
        route: "admin.payment.home",
        children: null
      },
      income: {
        text: "Pendapatan",
        route: "admin.payment.partner",
        children: {
          dataBusiness: {
            text: "Mitra Business",
            route: "admin.payment.partner.business",
            children: null
          },
          dataPool: {
            text: "Mitra Pool",
            route: "admin.payment.partner.pool",
            children: null
          },
          dataSpace: {
            text: "Mitra Space",
            route: "admin.payment.partner.space",
            children: null
          },
          dataTransporter: {
            text: "Mitra Transporter",
            route: "admin.payment.partner.transporter",
            children: null
          }
        },
        shortKey: ["ctrl", "alt", "i"]
      },
      summary: {
        title: "All Income",
        text: "Summary Order",
        route: "admin.payment.income",
        children: {
          mitraBusiness: {
            title: "Mitra Bisnis",
            text: "Mitra Business",
            route: "admin.payment.income",
            children: null
          },
          MS: {
            title: "Mitra Space",
            text: "Mitra Space",
            route: "admin.payment.ms",
            children: null
          },
          MPW: {
            title: "MPW",
            text: "MPW",
            route: "admin.payment.mpw",
            children: null
          },
          MTAK: {
            title: "MTAK",
            text: "MTAK",
            route: "admin.payment.mtak",
            children: null
          },
          // MTAKab: {
          //   title: "MTAKab",
          //   text: "MTAKab",
          //   route: "admin.payment.mtakab",
          //   children: null
          // },
        },
        shortKey: ["ctrl", "alt", "i"]
      },
      withdraw: {
        text: "Pencairan Mitra",
        route: "admin.payment.withdraw.request",
        children : {
          detail: {
            text: "Detail Pencairan",
            route: "admin.payment.withdraw.request.detail"
          }
        },
        shortKey: ["ctrl", "alt", "i"]
      }
        // children: {
        //   request: {
        //     text: "Daftar Request",
        //     route: "admin.payment.withdraw.request",
        //     children: null
        //   }
          // pending: {
          //   text: "Pencairan Pending",
          //   route: "admin.payment.withdraw.pending",
          //   children: null
          // },
          // success: {
          //   text: "Pencairan Berhasil",
          //   route: "admin.payment.withdraw.success",
          //   children: null
          // }
        // },
    },
    shortKey: ["ctrl", "alt", "p"]
  },
  master: {
    icon: "setting",
    text: "Master",
    route: "admin.master",
    children: {
      district: {
        text: "Master Ongkir Kecamatan",
        route: "admin.master.pricing.district",
        children: null,
        default: true
      },
      customer: {
        text: "Master Customer",
        route: "admin.master.customer",
        children: null,
        title: "Data Customer"
      },
      partner: {
        text: "Master Mitra",
        route: "admin.master.partner",
        title: "All Mitra",
        children: {
          all: {
            text: "Master All Mitra",
            route: "admin.master.partner",
            children: null
          },
          employee: {
            text: "Master Karyawan",
            title: "All Karyawan",
            route: "admin.master.employee",
            children: null
          },
          transporter: {
            text: "Master Kendaraan",
            route: "admin.master.transporter",
            children: null
          }
        }
      }
    },
    shortKey: ["ctrl", "alt", "m"]
  },
  message: {
    icon: "mail",
    text: "Pesan",
    route: "admin.message",
    children: null,
    shortKey: ["ctrl", "alt", "c"]
  }
};

export default main;
