export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Jenis Mitra",
    key: "partner_type",
    scopedSlots: { customRender: "partner_type" }
  },
  {
    title: "Kode Mitra",
    key: "partner_code",
    scopedSlots: { customRender: "partner_code" }
  },
  {
    title: "No HP / Email",
    key: "phone_email",
    scopedSlots: { customRender: "phone_email" }
  },
  {
    title: "Jabatan",
    dataIndex: "role",
    key: "role",
    scopedSlots: { customRender: "role" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
