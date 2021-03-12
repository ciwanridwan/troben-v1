const main = {
  home: {
    icon: "home",
    text: "Semua",
    route: "partner.cashier.home",
    children: {
      order: {
        title: "All Order",
        text: "Data Order",
        route: "partner.cashier.home.all",
        children: null
      },
      waiting: {
        title: "Menunggu",
        text: "Menunggu",
        route: "partner.cashier.home.waiting",
        children: {
          customer: {
            title: "Menunggu Konfirmasi Pelanggan",
            text: "Menunggu Konfirmasi Pelanggan",
            route: "partner.cashier.home.waiting.confirmation.customer",
            children: null
          },
          payment: {
            title: "Menunggu Konfirmasi Pembayaran",
            text: "Menunggu Konfirmasi Pembayaran",
            route: "partner.cashier.home.waiting.confirmation.payment",
            children: null
          },
          revision: {
            title: "Menunggu Konfirmasi Revisi",
            text: "Menunggu Konfirmasi Revisi",
            route: "partner.cashier.home.waiting.confirmation.revision",
            children: null
          }
        }
      },
      processed: {
        title: "Telah Diproses",
        text: "Telah Diproses",
        route: "partner.cashier.processed",
        children: null
      },
      done: {
        title: "Selesai",
        text: "Selesai",
        route: "partner.cashier.done",
        children: null
      },
      cancel: {
        title: "Selesai",
        text: "Selesai",
        route: "partner.cashier.cancel",
        children: null
      }
    },
    shortKey: ["ctrl", "alt", "d"]
  }
};

export default main;
