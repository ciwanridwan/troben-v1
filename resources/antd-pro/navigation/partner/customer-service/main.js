const main = {
  home: {
    icon: "home",
    text: "Home",
    route: "partner.customer_service.home",
    children: {
      order: {
        title: "All Order",
        text: "Data Order",
        route: "partner.customer_service.home.order",
        children: {
          pickup: {
            title: "All Order",
            text: "Permintaan Penjemputan",
            route: "partner.customer_service.home.order.pickup",
            children: null
          },
          taken: {
            title: "All Order",
            text: "Order yang diambil",
            route: "partner.customer_service.home.order.taken",
            children: null
          },
          passed: {
            title: "All Order",
            text: "Order yang dilewatkan",
            route: "partner.customer_service.home.order.passed",
            children: null
          },
          walkin: {
            title: "Create Order",
            text: "Buat Pesanan Walkin",
            route: "partner.customer_service.home.order.walkin.create",
            children: null,
            display: false
          }
        }
      },
      waiting: {
        title: "Menunggu",
        text: "Menunggu",
        route: "partner.customer_service.home.waiting",
        children: {
          pickup: {
            title: "Menunggu",
            text: "Menunggu Konfirmasi Pelanggan",
            route: "partner.customer_service.home.waiting.confirmation",
            children: null
          },
          taken: {
            title: "Menunggu",
            text: "Menunggu Pembayaran",
            route: "partner.customer_service.home.waiting.payment",
            children: null
          }
        }
      },
      processed: {
        title: "Telah diproses",
        text: "Telah diproses",
        route: "partner.customer_service.home.processed",
        children: null
      },
      done: {
        title: "Selesai",
        text: "Selesai",
        route: "partner.customer_service.home.done",
        children: null
      },
      cancel: {
        title: "Cancel",
        text: "Cancel",
        route: "partner.customer_service.home.cancel",
        children: null
      }
    },
    shortKey: ["ctrl", "alt", "d"]
  }
};

export default main;
