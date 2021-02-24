<?php

return [
	
 
		'extensions' => [
			'isPageLoadSearch' => true
		],
		
		'operationName' => 'SearchRequestQuery',
		'query' => 'query SearchRequestQuery($request: SearchResultRequest!, $filterCounts: Boolean!, $vrbo_web_global_messaging_alert: Boolean!, $vrbo_web_global_messaging_banner: Boolean!, $Vrbo_reco_large_search_destino: Boolean!) {
	  results: search(request: $request) {
		...querySelectionSet
		...DestinationBreadcrumbsSearchResult
		...DestinationCarouselSearchResult @include(if: $Vrbo_reco_large_search_destino)
		...DestinationMessageSearchResult
		...FilterCountsSearchRequestResult
		...HitCollectionSearchResult
		...ADLSearchResult
		...MapSearchResult
		...ExpandedGroupsSearchResult
		...PagerSearchResult
		...SearchTermCarouselSearchResult
		...InternalToolsSearchResult
		...SEOMetaDataParamsSearchResult
		...GlobalInlineMessageSearchResult @include(if: $vrbo_web_global_messaging_alert)
		...GlobalBannerContainerSearchResult @include(if: $vrbo_web_global_messaging_banner)
		...FlexibleDatesSearchResult
		__typename
	  }
	  ...RequestMarkerFragment
	}

	fragment querySelectionSet on SearchResult {
	  id
	  typeaheadSuggestion {
		uuid
		term
		name
		__typename
	  }
	  geography {
		lbsId
		gaiaId
		location {
		  latitude
		  longitude
		  __typename
		}
		isGeocoded
		shouldShowMapCentralPin
		__typename
	  }
	  propertyRedirectUrl
	  __typename
	}

	fragment DestinationBreadcrumbsSearchResult on SearchResult {
	  destination {
		breadcrumbs {
		  name
		  url
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment DestinationCarouselSearchResult on SearchResult {
	  destinationRecommendationResponse(size: 8, target: SERP_LARGE_SEARCH_TERM_DESTINATION) {
		...DestinationCarouselRecommendedDestinationResponse
		__typename
	  }
	  __typename
	}

	fragment DestinationCarouselRecommendedDestinationResponse on RecommendedDestinationResponse {
	  clientRequestId
	  recommendedDestinations {
		searchTermUuid
		imageHref
		recommendationModel
		breadcrumbs {
		  place {
			name {
			  simple
			  full
			  __typename
			}
			__typename
		  }
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment HitCollectionSearchResult on SearchResult {
	  page
	  pageSize
	  queryUUID
	  listings {
		...HitListing
		__typename
	  }
	  pinnedListing {
		headline
		listing {
		  ...HitListing
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment HitListing on Listing {
	  virtualTourBadge {
		name
		id
		helpText
		__typename
	  }
	  amenitiesBadges {
		name
		id
		helpText
		__typename
	  }
	  multiUnitProperty
	  images {
		altText
		c6_uri
		c9_uri
		mab {
		  banditId
		  payloadId
		  campaignId
		  cached
		  arm {
			level
			imageUrl
			categoryName
			__typename
		  }
		  __typename
		}
		__typename
	  }
	  ...HitInfoListing
	  __typename
	}

	fragment HitInfoListing on Listing {
	  listingId
	  ...HitInfoDesktopListing
	  ...HitInfoMobileListing
	  ...PriceListing
	  __typename
	}

	fragment HitInfoDesktopListing on Listing {
	  detailPageUrl
	  instantBookable
	  minStayRange {
		minStayHigh
		minStayLow
		__typename
	  }
	  listingId
	  rankedBadges(rankingStrategy: SERP) {
		id
		helpText
		name
		__typename
	  }
	  propertyId
	  propertyMetadata {
		headline
		__typename
	  }
	  superlativesBadges: rankedBadges(rankingStrategy: SERP_SUPERLATIVES) {
		id
		helpText
		name
		__typename
	  }
	  unitMetadata {
		unitName
		__typename
	  }
	  webRatingBadges: rankedBadges(rankingStrategy: SRP_WEB_RATING) {
		id
		helpText
		name
		__typename
	  }
	  ...DetailsListing
	  ...GeoDistanceListing
	  ...PriceListing
	  ...RatingListing
	  ...UrgencyMessageListing
	  ...MultiUnitHitListing
	  __typename
	}

	fragment DetailsListing on Listing {
	  bathrooms {
		full
		half
		toiletOnly
		__typename
	  }
	  bedrooms
	  propertyType
	  sleeps
	  petsAllowed
	  spaces {
		spacesSummary {
		  area {
			areaValue
			__typename
		  }
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment GeoDistanceListing on Listing {
	  geoDistance {
		text
		relationType
		__typename
	  }
	  __typename
	}

	fragment PriceListing on Listing {
	  priceSummary: priceSummary {
		priceAccurate
		...PriceSummaryTravelerPriceSummary
		__typename
	  }
	  priceSummarySecondary: priceSummary(summary: "displayPriceSecondary") {
		...PriceSummaryTravelerPriceSummary
		__typename
	  }
	  priceLabel: priceSummary(summary: "priceLabel") {
		priceTypeId
		pricePeriodDescription
		__typename
	  }
	  __typename
	}

	fragment PriceSummaryTravelerPriceSummary on TravelerPriceSummary {
	  priceTypeId
	  edapEventJson
	  formattedAmount
	  roundedFormattedAmount
	  pricePeriodDescription
	  __typename
	}

	fragment RatingListing on Listing {
	  averageRating
	  reviewCount
	  __typename
	}

	fragment UrgencyMessageListing on Listing {
	  unitMessage(assetVersion: 1) {
		...UnitMessageUnitMessage
		__typename
	  }
	  __typename
	}

	fragment UnitMessageUnitMessage on UnitMessage {
	  iconText {
		message
		icon
		messageValueType
		__typename
	  }
	  __typename
	}

	fragment MultiUnitHitListing on Listing {
	  propertyMetadata {
		propertyName
		__typename
	  }
	  propertyType
	  listingId
	  ...MultiUnitDropdownListing
	  ...MultiUnitModalListing
	  __typename
	}

	fragment MultiUnitDropdownListing on Listing {
	  ...MultiUnitListWrapperListing
	  __typename
	}

	fragment MultiUnitListWrapperListing on Listing {
	  listingNamespace
	  listingNumber
	  __typename
	}

	fragment MultiUnitModalListing on Listing {
	  ...MultiUnitListWrapperListing
	  __typename
	}

	fragment HitInfoMobileListing on Listing {
	  detailPageUrl
	  instantBookable
	  minStayRange {
		minStayHigh
		minStayLow
		__typename
	  }
	  listingId
	  rankedBadges(rankingStrategy: SERP) {
		id
		helpText
		name
		__typename
	  }
	  propertyId
	  propertyMetadata {
		headline
		__typename
	  }
	  superlativesBadges: rankedBadges(rankingStrategy: SERP_SUPERLATIVES) {
		id
		helpText
		name
		__typename
	  }
	  unitMetadata {
		unitName
		__typename
	  }
	  webRatingBadges: rankedBadges(rankingStrategy: SRP_WEB_RATING) {
		id
		helpText
		name
		__typename
	  }
	  ...DetailsListing
	  ...GeoDistanceListing
	  ...PriceListing
	  ...RatingListing
	  ...UrgencyMessageListing
	  ...MultiUnitHitListing
	  __typename
	}

	fragment ExpandedGroupsSearchResult on SearchResult {
	  expandedGroups {
		...ExpandedGroupExpandedGroup
		__typename
	  }
	  __typename
	}

	fragment ExpandedGroupExpandedGroup on ExpandedGroup {
	  listings {
		...HitListing
		...MapHitListing
		__typename
	  }
	  mapViewport {
		neLat
		neLong
		swLat
		swLong
		__typename
	  }
	  __typename
	}

	fragment MapHitListing on Listing {
	  ...HitListing
	  geoCode {
		latitude
		longitude
		__typename
	  }
	  __typename
	}

	fragment FilterCountsSearchRequestResult on SearchResult {
	  id
	  resultCount
	  filterGroups {
		groupInfo {
		  name
		  id
		  __typename
		}
		filters {
		  count @include(if: $filterCounts)
		  checked
		  filter {
			id
			name
			refineByQueryArgument
			description
			__typename
		  }
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment MapSearchResult on SearchResult {
	  mapViewport {
		neLat
		neLong
		swLat
		swLong
		__typename
	  }
	  page
	  pageSize
	  listings {
		...MapHitListing
		__typename
	  }
	  pinnedListing {
		listing {
		  ...MapHitListing
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment PagerSearchResult on SearchResult {
	  fromRecord
	  toRecord
	  pageSize
	  pageCount
	  page
	  resultCount
	  __typename
	}

	fragment DestinationMessageSearchResult on SearchResult {
	  destinationMessage(assetVersion: 4) {
		iconTitleText {
		  title
		  message
		  icon
		  messageValueType
		  link {
			linkText
			linkHref
			__typename
		  }
		  __typename
		}
		...DestinationMessageDestinationMessage
		__typename
	  }
	  __typename
	}

	fragment DestinationMessageDestinationMessage on DestinationMessage {
	  iconText {
		message
		icon
		messageValueType
		__typename
	  }
	  __typename
	}

	fragment ADLSearchResult on SearchResult {
	  parsedParams {
		q
		coreFilters {
		  adults
		  children
		  pets
		  minBedrooms
		  maxBedrooms
		  minBathrooms
		  maxBathrooms
		  minNightlyPrice
		  maxNightlyPrice
		  minSleeps
		  __typename
		}
		dates {
		  arrivalDate
		  departureDate
		  __typename
		}
		sort
		__typename
	  }
	  page
	  pageSize
	  pageCount
	  resultCount
	  fromRecord
	  toRecord
	  pinnedListing {
		listing {
		  listingId
		  __typename
		}
		__typename
	  }
	  listings {
		listingId
		__typename
	  }
	  filterGroups {
		filters {
		  checked
		  filter {
			groupId
			id
			__typename
		  }
		  __typename
		}
		__typename
	  }
	  geography {
		lbsId
		name
		description
		location {
		  latitude
		  longitude
		  __typename
		}
		primaryGeoType
		breadcrumbs {
		  name
		  countryCode
		  location {
			latitude
			longitude
			__typename
		  }
		  primaryGeoType
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment RequestMarkerFragment on Query {
	  requestmarker
	  __typename
	}

	fragment SearchTermCarouselSearchResult on SearchResult {
	  discoveryXploreFeeds {
		results {
		  id
		  title
		  items {
			... on SearchDiscoveryFeedItem {
			  type
			  imageHref
			  place {
				uuid
				name {
				  full
				  simple
				  __typename
				}
				__typename
			  }
			  __typename
			}
			__typename
		  }
		  __typename
		}
		__typename
	  }
	  typeaheadSuggestion {
		name
		__typename
	  }
	  __typename
	}

	fragment InternalToolsSearchResult on SearchResult {
	  internalTools {
		searchServiceUrl
		__typename
	  }
	  __typename
	}

	fragment SEOMetaDataParamsSearchResult on SearchResult {
	  page
	  resultCount
	  pageSize
	  geography {
		name
		lbsId
		breadcrumbs {
		  name
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment GlobalInlineMessageSearchResult on SearchResult {
	  globalMessages {
		...GlobalInlineAlertGlobalMessages
		__typename
	  }
	  __typename
	}

	fragment GlobalInlineAlertGlobalMessages on GlobalMessages {
	  alert {
		action {
		  link {
			href
			text {
			  value
			  __typename
			}
			__typename
		  }
		  __typename
		}
		body {
		  text {
			value
			__typename
		  }
		  link {
			href
			text {
			  value
			  __typename
			}
			__typename
		  }
		  __typename
		}
		id
		severity
		title {
		  value
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment GlobalBannerContainerSearchResult on SearchResult {
	  globalMessages {
		...GlobalBannerGlobalMessages
		__typename
	  }
	  __typename
	}

	fragment GlobalBannerGlobalMessages on GlobalMessages {
	  banner {
		body {
		  text {
			value
			__typename
		  }
		  link {
			href
			text {
			  value
			  __typename
			}
			__typename
		  }
		  __typename
		}
		id
		severity
		title {
		  value
		  __typename
		}
		__typename
	  }
	  __typename
	}

	fragment FlexibleDatesSearchResult on SearchResult {
	  percentBooked {
		currentPercentBooked
		__typename
	  }
	  __typename
	}

	',

		'variables'=> [
			'filterCounts'=> true,
			'request'=> [
				'coreFilters'=> [
					'maxBathrooms'=> null,
					'maxBedrooms'=> null,
					'maxNightlyPrice'=> $maxNightlyPrice,
					'maxTotalPrice'=> null,
					'minBathrooms'=> null,
					'minBedrooms'=> null,
					'minNightlyPrice'=> $minNightlyPrice,
					'minTotalPrice'=> null,
					'pets'=> 0
				],
				'q' => $queryLocation,
//				'q' => "tempe-arizona-united-states-of-america",
	//			'q' => "seminyak-bali-indonesia",
				/*
				'dates'=> [
					'arrivalDate'=> '2021-05-01',
					'departureDate'=> '2021-05-31'
				],
				*/
				'filters'=> [],
				'filterVersion'=> '1',
				'paging'=> [
					'page' => $page,
					'pageSize' => 50
				]
			],
			'Vrbo_reco_large_search_destino'=> false,
			'vrbo_web_global_messaging_alert'=> true,
			'vrbo_web_global_messaging_banner'=> true
		]	
	
];
	