export type Simple = number | boolean | string | null | undefined
export type Data = Simple | { [key: string]: Data } | Data[]

export function isSimple(x: unknown): x is Simple {
  return x == null || typeof x === 'string' || typeof x === 'number' || typeof x === 'boolean'
}

export type ID = number | string
export type Resource = {
  [key: string]: Data
}
// export type ResourceWithId = Resource & { id: ID };

export type StringMap = { [s: string]: string }
export type CsvRowGenerator = (rowData: StringMap, i: number, list: StringMap[]) => string
export type CsvFieldDef = {
  header: string
  value: string | CsvRowGenerator
}
export type CsvConfig = {
  filename: string | null
  fields: CsvFieldDef[]
}

export type SortOrder = 'asc' | 'desc'
export type Sort = {
  key: string
  order: SortOrder
}

export type AllRequestParams = {
  sort?: Sort[]
  search?: StringMap | string
}

export type AllResponse<T extends Resource> = {
  data: T[]
}

export type ListRequestParams = AllRequestParams & {
  page?: number
  perPage?: number
}

export type ListResponse<T extends Resource> = AllResponse<T> & {
  total: number
  page: number
}

export type ApiResponse = {
  success: boolean | number | string
  error?: string
  errors?: string[]
}

type Renderer = (item: unknown) => string

type Option = { value: Simple; title: string }

type Rule = (v: string) => true | string

export type Field = {
  key: string
  title: string
  search?: boolean
  sortable?: boolean
  list?: boolean
  options?: Option[]
  renderer?: Renderer
  edit?: boolean
  row?: boolean
  editor?: string
  rules?: Rule[]
}

export interface Position {
  lat: number
  lng: number
  updated_at: string
}
