export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Jenis Mitra",
    dataIndex: "type",
    key: "type",
    scopedSlots: { customRender: "type" }
  },
  {
    title: "Kode Mitra",
    key: "code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "Nama Owner",
    key: "name",
    scopedSlots: { customRender: "name" }
  },
  {
    title: "Kontak",
    key: "contact_phone",
    scopedSlots: { customRender: "contact_phone" }
  },
  {
    title: "Email",
    key: "contact_email",
    scopedSlots: { customRender: "contact_email" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
