export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Kode Mitra",
    dataIndex: "partner.code",
    key: "code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "No Order",
    dataIndex: "barcode",
    key: "type",
    scopedSlots: { customRender: "type" }
  },
  {
    title: "Keterangan",
    dataIndex: "desc",
    key: "name",
    scopedSlots: { customRender: "name" }
  },
  {
    title: "Debit",
    dataIndex: "price.debit",
    key: "debit",
    scopedSlots: { customRender: "debit" }
  },
  {
    title: "Kredit",
    dataIndex: "price.credit",
    key: "credit",
    scopedSlots: { customRender: "credit" }
  },
  {
    title: "Tanggal",
    dataIndex: "created_at",
    key: "date",
    scopedSlots: { customRender: "date" }
  }
];
