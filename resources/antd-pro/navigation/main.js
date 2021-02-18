const main = {
  home: {
    icon: "home",
    text: "Home",
    route: "admin.home",
    children: null,
    shortKey: ["ctrl", "alt", "d"]
  },
  payment: {
    icon: "setting",
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
  }
};

export default main;
