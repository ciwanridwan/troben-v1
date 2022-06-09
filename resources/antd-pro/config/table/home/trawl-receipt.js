import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colspan: 1,
    classes: ["trawl-text-center"]
  },
  {
    title: "Nomor Resi",
    dataIndex: "code.content",
    scopedSlots: { customRender: "id_order" },
    colspan: 5,
    classes: [""]
  },
  {
    title: "Mitra Penerima",
    colspan: 5,
    scopedSlots: { customRender: "partner" },
    classes: "trawl-text-left",
      customRender: (text, row, index) => {
        return {
          children: row.deliveries[0]?.partner?.code
            ? row.deliveries[0].partner.code
            : ""
        };
  }
  },
  {
    title: "Alamat Tujuan",
    key: "address",
    dataIndex: "receiver_address",
    colspan: 5
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    colspan: 5,
    classes: ["trawl-text-center"],
    customRender(text, row, index) {
      return moment(text).format("ddd, DD MMM YYYY HH:mm:ss");
    }
  }
];
