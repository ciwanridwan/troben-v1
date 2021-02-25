export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Jenis Mitra",
    dataIndex: "partner.type",
    key: "type",
    scopedSlots: { customRender: "type" }
  },
  {
    title: "Kode Mitra",
    dataIndex: "partner.code",
    key: "code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "Nama Pegawai",
    dataIndex: "name",
    key: "name",
    scopedSlots: { customRender: "name" }
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
