const main = {
  home: {
    icon: "home",
    text: "Home",
    route: "admin.home",
    children: null,
    shortKey: ["ctrl", "alt", "d"]
  },
  history: {
    icon: "history",
    text: "Riwayat",
    route: "admin.history",
    children: {
      cancel: {
        text: "Data Cancel Order",
        route: "admin.history.cancel",
        children: null
      },
      paid: {
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
      partner_income: {
        text: "Pendapatan Mitra",
        route: "admin.payment.partner_income",
        children: null,
        shortKey: ["ctrl", "alt", "i"]
      },
      withdraw: {
        text: "Request Pencairan",
        route: "admin.payment.withdraw",
        children: {
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
        route: "admin.master.charge.district",
        children: null
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
            route: "admin.master.employee",
            children: null
          },
          vehicle: {
            text: "Master Kendaraan",
            route: "admin.master.vehicle",
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
