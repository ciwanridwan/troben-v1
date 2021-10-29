export default [
  // {
  //   title: "No",
  //   key: "number",
  //   dataIndex: "number",
  //   scopedSlots: { customRender: "number" }
  // },
  // {
  //   title: "Jenis Mitra",
  //   dataIndex: "type",
  //   key: "type",
  //   scopedSlots: { customRender: "type" }
  // },
  {
    title: "Kode Mitra",
    key: "partner_code",
    classes: ["trawl-text-center"],
    scopedSlots: { customRender: "partner_code" }
  },
  {
    title: "Nama Mitra",
    key: "partner_name",
    classes: ["trawl-text-center"],
    scopedSlots: { customRender: "partner_name" }
  },
  {
    title: "Kota / Kabupaten",
    key: "partner_geo_regency",
    classes: ["trawl-text-center"],
    scopedSlots: { customRender: "partner_geo_regency" }
  },
  {
    title: "Balance",
    key: "balance",
    classes: ["trawl-text-center"],
    scopedSlots: { customRender: "balance" }
  },
];
