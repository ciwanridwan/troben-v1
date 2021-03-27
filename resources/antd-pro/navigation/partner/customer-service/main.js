const main = {
  home: {
    icon: "home",
    text: "Home",
    route: "partner.customer_service.home",
    children: {
      order: {
        title: "All Order",
        text: "Data Order",
        route: "partner.customer_service.order",
        children: {
          pickup: {
            title: "All Order",
            text: "Permintaan Penjemputan",
            route: "partner.customer_service.order.pickup",
            children: null
          },
          taken: {
            title: "All Order",
            text: "Order yang diambil",
            route: "partner.customer_service.order.taken",
            children: null
          },
          passed: {
            title: "All Order",
            text: "Order yang dilewatkan",
            route: "partner.customer_service.order.passed",
            children: null
          }
        }
      },
      waiting: {
        title: "Menunggu",
        text: "Menunggu",
        route: "partner.customer_service.order.waiting",
        children: null
      },
      processed: {
        title: "Telah diproses",
        text: "Telah diproses",
        route: "partner.customer_service.order.processed",
        children: null
      },
      done: {
        title: "Selesai",
        text: "Selesai",
        route: "partner.customer_service.order.done",
        children: null
      },
      cancel: {
        title: "All Delivery Order",
        text: "Data Delivery Order",
        route: "partner.customer_service.order.cancel",
        children: null
      }
    },
    shortKey: ["ctrl", "alt", "d"]
  }
};

export default main;
