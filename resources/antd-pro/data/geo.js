const geo = {
  data() {
    return {
      filterGeo: {
        per_page: -1
      },
      origin_provinces: [],
      origin_regencies: [],
      origin_districts: [],
      origin_sub_districts: [],
      destination_provinces: [],
      destination_regencies: [],
      destination_districts: [],
      destination_sub_districts: [],
    }
  },
  methods: {
    async getGeo(isOrigin = true, status = "province", params = {}) {
      this.loading = true;
      const { data } = await this.$http
        .get(this.routeUri("admin.geo"), {
          params: {
            type: status,
            ...this.filter,
            ...params
          }
        })
      this.putGeoToData(isOrigin, status, data.data)
      this.loading = false
    },
    putGeoToData(isOrigin = true, status, data) {
      switch (status) {
        case "province":
          isOrigin ? (this.origin_provinces = data) : (this.destination_provinces = data);
          break;
        case "regency":
          isOrigin ? (this.origin_regencies = data) : (this.destination_regencies = data);
          break;
        case "district":
          isOrigin ? (this.origin_districts = data) : (this.destination_districts = data);
          break;
        case "sub_district":
          isOrigin ? (this.origin_sub_districts = data) : (this.destination_sub_districts = data);
          break;
      }
    },
  }
}

export default geo;
