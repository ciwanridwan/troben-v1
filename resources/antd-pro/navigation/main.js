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
        text: "Data Resi",
        route: "admin.home.resi",
        children: null
      },
      manifest: {
        title: "All Manifest",
        text: "Data Manifest",
        route: "admin.home.manifest",
        children: null
      },
      delivery: {
        title: "All Delivery Order",
        text: "Data Delivery Order",
        route: "admin.home.delivery",
        children: null
      }
    },
    shortKey: ["ctrl", "alt", "d"]
  },
  history: {
    icon: "history",
    text: "Riwayat",
    route: "admin.history",
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
    route: "admin.payment",
    children: {
      income: {
        text: "Pendapatan Mitra",
        route: "admin.payment.income",
        children: null,
        shortKey: ["ctrl", "alt", "i"]
      },
      withdraw: {
        text: "Pencairan Mitra",
        route: "admin.payment.withdraw",
        children: {
          request: {
            text: "Request Pencairan",
            route: "admin.payment.withdraw.request",
            children: null
          },
          pending: {
            text: "Pencairan Pending",
            route: "admin.payment.withdraw.pending",
            children: null
          },
          success: {
            text: "Pencairan Berhasil",
            route: "admin.payment.withdraw.success",
            children: null
          }
        },
        shortKey: ["ctrl", "alt", "i"]
      }
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
