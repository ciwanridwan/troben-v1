import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colspan: 1,
    classes: ["trawl-text-center"]
  },
  {
    title: "ID Order",
    scopedSlots: { customRender: "id_order" },
    colspan: 6,
    classes: ["trawl-text-left"]
  },
  {
    title: "Mitra Penerima",
    colspan: 5,
    scopedSlots: { customRender: "partner" },
    classes: "trawl-text-left",
    // customRender: (text, row, index) => {
    //   return {
    //     children: row.deliveries[0]?.partner?.name
    //       ? row.deliveries[0].partner.name
    //       : ""
    //   };
    // }
  },
  {
    title: "Lokasi Pengiriman",
    key: "address",
    classes: "trawl-text-left",
    colspan: 5,
    scopedSlots: { customRender: "address" }
  },
  {
    title: "Order By",
    key: "type",
    scopedSlots: { customRender: "type" },
    sorter: true,
    classes: "trawl-text-center",
    colspan: 2
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    colspan: 4,
    classes: ["trawl-text-center"],
    customRender(text, row, index) {
      return moment(text).format("ddd, DD MMM YYYY HH:mm:ss");
    }
  }
];
