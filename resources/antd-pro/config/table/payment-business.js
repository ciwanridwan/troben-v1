import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colspan: 1,
    classes: ["trawl-text-center"]
  },
  {
    title: "Kode Mitra",
    colspan: 3,
    classes: ["trawl-text-center trawl-text-bolder"],
    customRender: (text, row, index) => {
      return {
        children: row.history_business[0]?.partner?.code
          ? row.history_business[0].partner.code
          : ""
      };
    }
  },
  {
    title: "ID Order",
    scopedSlots: { customRender: "id_order" },
    colspan: 5,
    classes: ["trawl-text-left"]
  },
  {
    title: "Keterangan",
    key: "detail",
    colspan: 4,
    scopedSlots: { customRender: "detail" }
  },
  {
    title: "Pendapatan",
    key: "balance",
    scopedSlots: { customRender: "balance" },
    sorter: true,
    colspan: 3
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    key: "created_at",
    scopedSlots: { customRender: "created_at" },
    colspan: 5,
    customRender(text, row, index) {
      return moment(text).format("ddd, DD MMM YYYY HH:mm:ss");
    }
  }
];
